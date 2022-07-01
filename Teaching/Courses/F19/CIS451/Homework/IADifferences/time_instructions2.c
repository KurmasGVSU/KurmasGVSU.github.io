
/******************************************************************
 * time_instructions2.c
 *
 * Time instruction sequences of varying lengths
 * (*without* using ICOS)
 *
 * (C) 2019 Zachary Kurmas
 ******************************************************************/

#include <stdio.h>

// It is assumed that a separate file (either C or assembly)  will
// define and populate this array.
// It represents the list of functions to be timed.
extern int num_functions;
extern unsigned long (*functions[5000])();
extern unsigned num_ops[5000];

///////////////////////////////////////////////////////////////////////
//
// Repeatedly time the code passed in function_to_time and report 
// statistics.
//
///////////////////////////////////////////////////////////////////////
static void take_measurement (unsigned long (*function_to_time)(),
				              unsigned num_ops) {

  int iterations = 2500;
  int warm_up =500;

  // Warm up the processor with "untimed" runs
  // However, we keep track of the minimum so that the 
  // compiler doesn't optimize the loop away.
  unsigned long min = function_to_time();
  for (int i = 0; i < warm_up; i++) {
    unsigned long value = function_to_time();
    min = (value < min) ? value : min;
  }

  // Don't take a minimum from the "warm up"
  
  min *= 100;
  // (sum and sum_sq need to be doubles, otherwise they might overflow)
  double sum = 0;
  double sum_sq = 0;
  unsigned long max = 0;
  double variance = 0;
  for (int i = 1; i <= iterations; i++) {

    // Generate a data piont
    unsigned long value = function_to_time();

    // Do the "recordkeeping" (update sum, min, max, etc.)
    double d_value = (double)value;
    sum += d_value;
    sum_sq += d_value*d_value; 
    if (value > max) {
      max = value;
    }
    if (value < min) {
      min = value;
    }
    // Take care not to divide by 0 if i < 2
    variance = i>=2 ? (i*sum_sq - sum*sum)/(i*(i-1)) : 0;

    // printf("   %5lu, %5lu  %10lu %0.2f\n", num_ops, value, min, (sum/(double)i));
    if (i == iterations) {
      printf("%6d: avg=%0.2f min=%d (%0.2f) max=%d var=%0.2f\n", num_ops, ((double)sum) / i, min, ((double)min/num_ops), max, variance);
    }
  }// end


// printf("%5lu  %10lu %10lu\n", num_ops, min, (sum/iterations));

}


int main(int argc, char* argv[]) {
    for (int i = 0; i < num_functions; i++) {
    take_measurement(functions[i],  num_ops[i]);
  }
}

