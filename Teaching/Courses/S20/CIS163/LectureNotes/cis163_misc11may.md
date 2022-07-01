## Monday 11 May:

* {: .to-me} Review Lab 01 and talking about how to write tests for P1.
  * Look at gt, lt, eq for month day and year for `compareTo`
  * Look at the code that counts days wrong. (`BrokenDate1d`)
  * Use this to emphasize white box testing.
* {: .q} Is the difference between `.isLeapYear(int)` and `#isLeapYear()` clear?
* {: .q} Any other questions about what the different methods in the project should do.
* Remember, do the two-digit year stuff last.
* {: .to-me} Talk through how to test other P1 methods and what is expected for HW 1

## Enums

* Enums:
  * Suppose I want to represent days of the week using integers `int getDayOfWeek()`. 
    <span>What are some problems?</span>{: .q}
    * Not always clear what the integers mean (is Sunday `0` or `1`?)  But, this can be handled with good documentation.
    * Bigger issue:  `int dayOfWeek = 8`.  It would be nice if we could prevent accidental assignment to invalid values.
  * Enum provides a limited set of values so this can't happen.  A `DayOfWeek` value is always a valid.
* Big picture: One key aspect of programming language design is to help programmers avoid mistakes.  (Or at least discover those mistakes as quickly as possible.)