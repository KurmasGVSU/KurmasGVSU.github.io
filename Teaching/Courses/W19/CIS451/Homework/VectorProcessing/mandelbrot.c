/****************************************************************************************
 *
 * Generate the Mandelbrot set
 *
 * Must be compiled with -mfma flag
 *
 * *************************************************************************************/

#include <stdio.h>
#include <immintrin.h>

void mandelbrot_c_regular(float x1, float y1, float x2, float y2,
                          int width, int height, unsigned short max_iters, unsigned short *image) {

  printf("Mandelbrot C Regular\n");

  float dx = (x2 - x1) / width, dy = (y2 - y1) / height;
  for (int j = 0; j < height; ++j)
    for (int i = 0; i < width; ++i) {

      float cr = x1 + dx * i;
      float ci = y1 + dy * j;

      float zr = 0.0;
      float zi = 0.0;

      float norm_z = 0.0;

      unsigned short count = 0;
      while ((count < max_iters) && (norm_z < 4.0)) {

        // Conceptually, we are calculating z = z*z + c
        // We do this by calculating the real and imaginary parts separately
        //
        // To see what is going on here, rewrite z and c  as (zr + zi) and (cr + ci), then
        // use "FOIL" to expand (zr + zi)(zr + zi) + cr + ci and collect like terms.
        // (Don't forget that i*i = -1.)
        float tzr = zr * zr - zi * zi + cr;
        zi = 2.0f * zr * zi + ci;
        zr = tzr;

        norm_z = zr * zr + zi * zi;

        ++count;
      }
      if (count == max_iters) {
        count = 0;
      }
      *image++ = count;
    }
}

void mandelbrot_c_avx(float x1, float y1, float x2, float y2,
                      int width, int height, unsigned short max_iters, unsigned short *image) {
  if (width % 8 != 0) {
    fprintf(stderr, "Width must be a multiple of 8");
    return;
  }

  printf("Mandelbrot C AVX\n");

  // dx and dy are the interval represented by each pixel in the output image.
  // (Or, in other words, dx and dy are the "real" distances we move forward from pixel to pixel.)
  float dx = (x2 - x1) / width;
  float dy = (y2 - y1) / height;

  float constants[] = {dx, dy, x1, y1, 1.0f, 4.0f};
  __m256 dx_ymm0 = _mm256_broadcast_ss(constants);   // all dx
  __m256 dy_ymm1 = _mm256_broadcast_ss(constants + 1); // all dy

  __m256 x1_ymm2 = _mm256_broadcast_ss(constants + 2); // all x1
  __m256 y1_ymm3 = _mm256_broadcast_ss(constants + 3); // all y1

  __m256 const1_ymm4 = _mm256_broadcast_ss(constants + 4); // all 1's (iter increments)
  __m256 const4_ymm5 = _mm256_broadcast_ss(constants + 5); // all 4's (comparisons)


  float incr[8] = {0.0f, 1.0f, 2.0f, 3.0f, 4.0f, 5.0f, 6.0f,
                   7.0f}; // used to reset the i position when j increases
  __m256 real_y_ymm6 = _mm256_xor_ps(dx_ymm0, dx_ymm0); // zero out real_y (foo xor foo == 0)

  for (int row = 0; row < height; row += 1) {
    __m256 column_offset_ymm7 = _mm256_loadu_ps(incr);  // set to 0,1,2,..,7

    for (int col = 0; col < width; col += 8) {

      // Calculate the real component of the points to process (i.e., the current "c")
      __m256 real_c_ymm8 = _mm256_mul_ps(column_offset_ymm7, dx_ymm0);
      real_c_ymm8 = _mm256_add_ps(real_c_ymm8, x1_ymm2);

      // Calculate the imaginary component of the points to process
      __m256 imaginary_c_ymm9 = _mm256_mul_ps(real_y_ymm6, dy_ymm1);       // y0 = row*dy
      imaginary_c_ymm9 = _mm256_add_ps(imaginary_c_ymm9, y1_ymm3);         // y0 = y1+row*dy


      // Initialize z to 0
      __m256 iteration_count_ymm10 = _mm256_xor_ps(dx_ymm0, dx_ymm0);  // zero out iteration counter

      // initialize z to 0 (both the real and imaginary component)
      __m256 real_z_ymm11 = iteration_count_ymm10;
      __m256 imaginary_z_ymm12 = iteration_count_ymm10;

      // Loop and see how many iterations it takes to for norm(z) to reach 4
      unsigned int test = 0;
      int iter = 0;
      do {
        __m256 real_z_sq_ymm13 = _mm256_mul_ps(real_z_ymm11, real_z_ymm11);
        __m256 imaginary_z_sq_ymm14 = _mm256_mul_ps(imaginary_z_ymm12, imaginary_z_ymm12);
        __m256 norm_z_ymm15 = _mm256_add_ps(real_z_sq_ymm13, imaginary_z_sq_ymm14);

        // Check whether norm z is < 4.  Returns 1 if true, 0 otherwise
        norm_z_ymm15 = _mm256_cmp_ps(norm_z_ymm15, const4_ymm5, _CMP_LT_OQ);

        // Create a bit mask based on whether the values in norm_z_ymm15 are positive or negative
        test = _mm256_movemask_ps(norm_z_ymm15) & 255U;
        norm_z_ymm15 = _mm256_and_ps(norm_z_ymm15, const1_ymm4);

        // get 1.0f or 0.0f in each field as counters
        // counters for each pixel iteration
        iteration_count_ymm10 = _mm256_add_ps(iteration_count_ymm10, norm_z_ymm15);
        norm_z_ymm15 = _mm256_mul_ps(real_z_ymm11, imaginary_z_ymm12);

        // Calculate z = z*z + c
        // let z = (zr + zi)(zr + zi) + cr + ci
        //       zr = zr^2 - zi^2 + cr
        real_z_ymm11 = _mm256_sub_ps(real_z_sq_ymm13, imaginary_z_sq_ymm14);
        real_z_ymm11 = _mm256_add_ps(real_z_ymm11, real_c_ymm8);

        //       zi = 2*zr*zi + ci
        imaginary_z_ymm12 = _mm256_add_ps(norm_z_ymm15, norm_z_ymm15);
        imaginary_z_ymm12 = _mm256_add_ps(imaginary_z_ymm12, imaginary_c_ymm9);


        ++iter;
      } while ((test != 0) && (iter < max_iters));


      // convert iterations to output values
      int top = (col + 7) < width ? 8 : width & 7;
      for (int k = 0; k < top; ++k) {
        int loc = col + k + row * width;
        unsigned short val = ((unsigned short) iteration_count_ymm10[k]);
        if (val == max_iters) {
          val = 0;
        }
        image[col + k + row * width] = val;
      }
      // next col position - increment each slot by 8
      column_offset_ymm7 = _mm256_add_ps(column_offset_ymm7, const4_ymm5);
      column_offset_ymm7 = _mm256_add_ps(column_offset_ymm7, const4_ymm5);
    }
    real_y_ymm6 = _mm256_add_ps(real_y_ymm6, const1_ymm4);
  }

}

void write_ppm(const char *filename, int width, int height, unsigned short *array) {

  FILE *output = fopen(filename, "w+");
  fprintf(output, "P3\n");
  fprintf(output, "%d %d\n", width, height);
  fprintf(output, "255\n");

  int count = 0;
  for (int i = 0; i < width; i++) {
    for (int j = 0; j < height; j++) {
      fprintf(output, "0 %d 0  ", array[count]);
      count++;
    }
    fprintf(output, "\n");
  }

  fclose(output);
}


void scale_linear(int width, int height, const unsigned short max_val, unsigned short* array) {
  unsigned short scale = max_val / (unsigned short) 255;
  int count = 0;
  for (int i = 0; i < width; i++) {
    for (int j = 0; j < height; j++) {
      array[count] = array[count] / scale;
      ++count;
    }
  }
}

void scale_quadratic(int width, int height, const unsigned short max_val, unsigned short* array) {

  unsigned long int maxSq = max_val * max_val;
  unsigned long int scale = maxSq / 255;

  int count = 0;


  for (int i = 0; i < width; i++) {
    for (int j = 0; j < height; j++) {
      unsigned long int current = array[count];
      unsigned long int rev1 = max_val - current;
      unsigned long int sq = rev1*rev1;
      unsigned long int rev2 = maxSq - sq;
      unsigned long int answer = rev2/scale;

      if (answer > 256) {
        fprintf(stderr, "Oops.  Programmer error!\n");
      }

      array[count] = (unsigned short)answer;
      ++count;
    }
  }
}


int main(int argc, char* argv[]) {
  const int width = 1800;
  const int height = 1800;
  const int max_iters = 4096;

  const char* output_file = "mandelbrot_c.ppm";
  if (argc > 1) {
    output_file = argv[1];
  }


  unsigned short array1[width * height];
  mandelbrot_c_regular(.29778, .48364, .29768, .48354, width, height, max_iters, array1);
  scale_quadratic(width, height, max_iters, array1);
  write_ppm(output_file, width, height, array1);
}
