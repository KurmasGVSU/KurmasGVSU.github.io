/****************************************************************************************
 *
 * Generate the Mandelbrot set using the built-in C++ complex library
 *
 * Code obtained from
 * https://software.intel.com/en-us/articles/introduction-to-intel-advanced-vector-extensions
 *
 * *************************************************************************************/

#include <complex>
#include <iostream>
#include <fstream>

void mandelbrotRegular(float x1, float y1, float x2, float y2,
                   int width, int height, int maxIters, unsigned short *image) {

  std::cout << "Mandelbrot C++ Regular" << std::endl;

  float dx = (x2 - x1) / width, dy = (y2 - y1) / height;
  for (int j = 0; j < height; ++j)
    for (int i = 0; i < width; ++i) {
      std::complex<float> c(x1 + dx * i, y1 + dy * j), z(0, 0);
      unsigned short count = 0;
      while ((count < maxIters) && (norm(z) < 4.0)) {
        z = z * z + c;
        ++count;
      }
      if (count >= maxIters) {
        count = 0;
      }
      *image++ = count;
    }
}

void writePPM(const char *fileName, int width, int height, unsigned short *array) {
  std::ofstream file;
  file.open(fileName);
  file << "P3" << std::endl;
  file << width << " " << height << std::endl;
  file << 255 << std::endl;

  int count = 0;
  for (int i = 0; i < width; i++) {
    for (int j = 0; j < height; j++) {
      file << "0 " << array[count] << " 0 ";
      count++;
    }
    file << std::endl;
  }

  file.close();
}

void scaleLinear(int width, int height, const unsigned short maxVal, unsigned short* array) {
  unsigned short scale = maxVal / (unsigned short) 255;
  int count = 0;
  for (int i = 0; i < width; i++) {
    for (int j = 0; j < height; j++) {
      array[count] = array[count] / scale;
      ++count;
    }
  }
}

void scaleQuadratic(int width, int height, const unsigned short maxVal, unsigned short* array) {

  unsigned long int maxSq = maxVal * maxVal;
  unsigned long int scale = maxSq / 255;

  int count = 0;


  for (int i = 0; i < width; i++) {
    for (int j = 0; j < height; j++) {
      unsigned long int current = array[count];
      unsigned long int rev1 = maxVal - current;
      unsigned long int sq = rev1*rev1;
      unsigned long int rev2 = maxSq - sq;
      unsigned long int answer = rev2/scale;

      if (answer > 256) {
        std::cerr << "Oops.  Programmer error!" << std::endl;
      }

      array[count] = (unsigned short)answer;
      ++count;
    }
  }
}


int main(int argc, char* argv[]) {

  const int width = 1200;
  const int height = 1200;
  const int maxIterations = 4096;

  const char* outputFile = "mandelbrot_cpp.ppm";
  if (argc > 1) {
    outputFile = argv[1];
  }

  unsigned short array1[width*height];
  mandelbrotRegular(.29778, .48364, .29768, .48354, width, height, maxIterations, array1);
  scaleQuadratic(width, height, maxIterations , array1);
  writePPM(outputFile, width, height, array1);
}
