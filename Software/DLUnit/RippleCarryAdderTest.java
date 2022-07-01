import org.junit.Assert;
import org.junit.FixMethodOrder;
import org.junit.Test;
import org.junit.runners.MethodSorters;

import static edu.gvsu.dlunit.DLUnit.*;

/**
 * JUnit-style test for a unsigned ripple-carry adder.
 */
@FixMethodOrder(MethodSorters.NAME_ASCENDING)
public class RippleCarryAdderTest {

  // A list of values used to thoroughly test adder.
  private long[] smallTestValues = {0, 1, 2, 3, 4, 5, 127, 128, 129, 0x700, 0x7A5};
  private long[] allTestValues = {0, 1, 3, 4, 9, 14, 15, 127, 128, 129, 0x1234, 0xABCD, 0xF000, 0xFFFC, 0xFFFE, 0xFFFF};


  // By making width an instance variable, I can easily convert this code to test adders of other widths:
  // Add a parameter to the constructor, then create a subclass for each width to be tested. (Remember, however,
  // that JUnit tests can have only one constructor, which must take no parameters --- hence the need for all the
  // subclasses.)
  private int width;


  public RippleCarryAdderTest() {
    width = 16;
  }

  // All the tests have the same form, so I created this helper method to avoid repetition.
  private void verifyOutputAndCarryOut(long inputA, long inputB, boolean carry_in, boolean check_overflow) {
    setPinUnsigned("InputA", inputA);
    setPinUnsigned("InputB", inputB);
    setPin("CarryIn", carry_in);
    run();

    // Calculate the expected value.  In the case of overflow, we ignore all but the
    // least significant `width` bits.
    long expected = (inputA + inputB + (carry_in ? 1 : 0)) % maxUnsigned();

    // Adding a custom message makes it easier to tell specifically which test failed.
    String message = String.format("Testing %d + %d (carry in: %b)", inputA, inputB, carry_in);
    Assert.assertEquals(message, expected, readPinUnsigned("Output"));

    if (check_overflow) {
       boolean carry_out_expected = ((inputA + inputB + (carry_in ? 1 : 0)) >= maxUnsigned());
       String message2 = String.format("Testing carry out for %d + %d (carry in: %b)", inputA, inputB, carry_in);
       Assert.assertEquals(message2, carry_out_expected, readPin("CarryOut"));
    }
  }

  // Shortcut for verifying the output only
  private void verifyOutput(long inputA, long inputB, boolean carry_in) {
    verifyOutputAndCarryOut(inputA, inputB, carry_in, false);
  }

  //
  // Normal inputs, no carry out, no carry in.
  //

  @Test
  public void a1_zero_plus_zero() {
    verifyOutput(0, 0, false);
  }

  @Test
  public void a2_zero_plus_one() {
    verifyOutput(0, 1, false);
  }

  @Test
  public void a3_one_plus_zero() {
    verifyOutput(0, 1, false);
  }

  @Test
  public void a4_medium_values() {
    verifyOutput(8216, 3719, false);
  }

  @Test
  public void a5_large_values() {
    verifyOutput(maxUnsigned() - 2, 1, false);
  }

  /* In general, each test should examine one set of inputs only. This approach
   * is practical only when testing a small set of well-chosen test cases (e.g.,
   * corner cases); however, I like to conduct more thorough/exhaustive tests on my
   * students' circuits.  This single test reports whether the complete set of inputs
   * all pass.   The `message` in verifyOutput is especially helpful in this case.
   */
  @Test
  public void a6_thoroughTestNoOverflowNoCarryIn() {
    for (long v1 : smallTestValues) {
      for (long v2 : smallTestValues) {
        verifyOutput(v1, v2, false);

        // must be called explicitly because we are running the simulator multiple times within a single JUnit test.
        reset();
      }
    }
  }

  //
  // Normal inputs, no carry out, carry in.
  //

  @Test
  public void b1_zero_plus_zero_carry_in() {
    verifyOutput(0, 0, true);
  }

  @Test
  public void b2_two_plus_three_carry_in() {
    verifyOutput(2, 3, true);
  }

  @Test
  public void b3_thoroughTestNoCarryOutCarryIn() {
    for (long v1 : smallTestValues) {
      for (long v2 : smallTestValues) {
        verifyOutput(v1, v2, true);
        // must be called explicitly because we are running the simulator multiple times within a single JUnit test.
        reset();
      }
    }
  }

  //
  // possible carry out
  //
  @Test
  public void c1_zero_plus_zero_check_carryOut() {
    verifyOutput(0, 0, false);
  }

  @Test
  public void c2_five_plus_10_check_carryOut() {
    verifyOutput(5, 10, false);
  }

  @Test
  public void c3_verifyCarryOut() {
    verifyOutput(maxUnsigned() - 1, 1, false);
  }

  @Test
  public void c4_thoroughTestCarryOut() {
    for (long v1 : allTestValues) {
      for (long v2 : allTestValues) {
        verifyOutput(v1, v2, false);
        // must be called explicitly because we are running the simulator multiple times within a single JUnit test.
        reset();

        verifyOutput(v1, v2, true);
        reset();
      }
    }
  }


  private long maxUnsigned() {
    return (1L << width);
  }
}
