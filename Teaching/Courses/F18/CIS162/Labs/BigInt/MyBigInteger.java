
/*************************************************************************
 *
 * Represents <em>non-negative</em> integers larger than 2^31.  This
 * class uses the "base10" method: The big integer is stored in an
 * array, where each array cell contains one base-10 digit.
 *
 * @author Zachary Kurmas
 *
 ***************************************************************************/
public class MyBigInteger {

  /**
   * This is the array that will store the digits of the big integer.
   */
  private int[] digits;

  /**
   * To make life simple, we'll assume that all of the {@code
   * BigInteger} objects use exactly 1024 digits (including leading
   * 0s).
   */
   static final int ARRAY_SIZE = 1024;

  /************************************************************************
   *
   * Construct a {@code BigInteger} object that to represent the
   * integer specified by the <code>String</code>.
   *
   * @param input a <code>String</code> representation of a very large
   * integer.  The <code>String</code> must contain only the characters
   * '0' through '9'.
   *
   * @throws NumberFormatException if {@code input} does not
   * represent a non-negative integer.
   *
   **********************************************************************/
  public MyBigInteger(String input) {

    // Throw an exception if the number requires more
    // than ARRAY_SIZE spaces.
    if (input.length() > ARRAY_SIZE) {
      throw new NumberFormatException("Input string must contain at most " +
          ARRAY_SIZE + " characters");
    }

    // TO DO #1: Instantiate the array digits  Its length
    // should be equal to ARRAY_SIZE.


    // TO DO #2: Now, use a loop to fill the digits with the
    // input.  See hints below.

    // Hint 1: In order to make the other methods easy to write,
    // fill the array "backwards".  In other words, put the ones
    // digit in array slot 0, put the 10s digit in array slot 1,
    // etc.

    // Hint 2: To turn the character at position p in String s
    // into an integer, use this trick:
    // int d = s.charAt(p) - '0';
    // do *not* put the line of code above into your code as is,
    // the variable names and loop index won't be correct.


  } // end constructor

  /************************************************************************
   *
   * Construct a {@code BigInteger} object that to represent the
   * integer specified by the <code>int n</code>
   *
   * @param n a "regular" positive integer.
   *
   **********************************************************************/
  public MyBigInteger(int n) {
    // This dirty trick simply turns the integer n into a String,
    // then calls the other constructor.
    this(n + "");
  }

  /************************************************************************
   *
   * Returns the array containing the big integer's individual digits.
   *
   * @return the array containing the big integer's individual digits.
   *
   **********************************************************************/
   int[] getDigits() {
    return digits;
  }


  /************************************************************************
   *
   * Returns a <code>String</code> representation of this object.  The
   * <code>String</code> returned should not contain any leading zeros.
   *
   * @return a <code>String</code> representation of this object.
   *
   ************************************************************************/
  public String toString() {
    // TO DO: Concatenate the individual digits into a single
    // String and return it.  Do not print leading zeros.

    return "";
  }


  /************************************************************************
   *
   * Returns <code>true</code> if both objects represent the same integer.
   *
   * @param n1 a big integer to be compared.
   * @param n2 a big integer to be compared.
   *
   * @return <code>true</code> if <code>n1</code> and
   * <code>n2</code> represent the same integer.
   *
   **********************************************************************/
  public static boolean equals(MyBigInteger n1, MyBigInteger n2) {
    // TO DO: Compare n1 and n2 and return true if and only if
    // they are equal. No, you cannot simply say "n1 == n2".  You
    // have to compare each digit in n1.digits and n2.digits
    // (kind of like comparing two Strings).
    return false;
  } // end equals


  /************************************************************************
   *
   * Returns <code>true</code> if <code>n1 < n2</code>.
   *
   * @param n1 a big integer to be compared.
   * @param n2 a big integer to be compared.
   *
   * @return <code>true</code> if <code>n1 < n2</code>.
   *
   **********************************************************************/
  public static boolean lessThan(MyBigInteger n1, MyBigInteger n2) {
    // TO DO:  Compare n1 and n2 and return true if and only if n1 < n2

    return false;


  }

  /************************************************************************
   *
   * Returns a <code>BigInteger</code> representing the sum of
   * <code>n1</code> and <code>n2</code>.
   *
   * @param n1 a big integer to be added
   * @param n2 a big integer to be added
   *
   * @return a <code>BigInteger</code> representing the sum of
   * <code>n1</code> and <code>n2</code>.
   *
   **********************************************************************/
  public static MyBigInteger add(MyBigInteger n1, MyBigInteger n2) {
    // Add n1 and n2 together and put the result in a new
    // BigInteger named answer.


    // TO DO #1: create a new BigInteger object named answer with value 0.


    // TO DO #2: write a loop that adds the individual digits of
    // n1 and n2 and put the result in answer just like you were
    // doing addition by hand.  You will be filling each slot in
    // the answer's digits array by hand.  Don't forget to put
    // only a single digit in each array slot and "carry" the 10s
    // value if necessary.


    // TO DO #4: return answer;
    return null;
  }


  /************************************************************************
   *
   * Returns a <code>BigInteger</code> representing the product of
   * <code>n1</code> and <code>n2</code>.
   *
   * @param n1 a big integer to be added
   * @param n2 a big integer to be added
   *
   * @return a <code>BigInteger</code> representing the product of
   * <code>n1</code> and <code>n2</code>.
   *
   **********************************************************************/
  public static MyBigInteger multiply(MyBigInteger n1, MyBigInteger n2) {

    // Save this until last.  Don't begin writing this method
    // unless you understand how the other methods work.

    return null;
  }
} // end BigInteger