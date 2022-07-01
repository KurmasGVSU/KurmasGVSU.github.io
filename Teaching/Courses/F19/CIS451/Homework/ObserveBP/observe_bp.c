#include <stdbool.h>
#include <stdio.h>

/* Array size */
#define SIZE 2048

/* Array containing branch directions */
bool values[SIZE];

/* Return the Intel time stamp counter.  This function inserts an
   assembly language call to rdstc, then returns that value from the
   function.
*/
static __inline__ unsigned long long rdtsc(void)
{
  unsigned long long int x;
  /* __asm__ volatile (".byte 0x0f, 0x31" : "=A" (x));*/
   __asm__ volatile ("rdtsc" : "=A" (x));
  return x;
}

/*  This condiditional compiler directive allows us to set the value
    of TAKE_OR_NOT from the command line using gcc's "-D" flag.  I
    used this construct instead of the more obvious command-line
    paramter because
    (1) The #define made the code shorter and cleaner.  (parsing argv can get messy)
    (2) I can try new ideas without modifying the code
*/
#ifndef TAKE_OR_NOT
#define TAKE_OR_NOT (which)
#endif

int main(int argc, char*argv[])
{
  srandom(rdtsc());

  /* Array Initialization Loop:
     Initialize the array that determines whether the branch is taken.
  */
  int i;
  for (i = 0; i < SIZE; i++) {
    bool which = random() %2;
    values[i] = TAKE_OR_NOT;
  }

  long long unsigned sum1 = 34038;;
  long long unsigned sum2 = 34037;

  long long unsigned start = rdtsc();
  for (i = 0; i < SIZE; i++) {
    if (values[i]) {
      sum1 *= 30943;
      sum1++;
    } else {
	sum2 *= 22891;
	sum2++;
      }
  }
   
  long long unsigned stop = rdtsc();
  printf("Sum1:  %llu\tSum2:  %llu\n", sum1, sum2);
  printf("Time: %llu\n", stop - start); 
}
