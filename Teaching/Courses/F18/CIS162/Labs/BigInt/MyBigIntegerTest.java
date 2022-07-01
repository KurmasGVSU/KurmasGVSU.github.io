
import org.junit.*;

import java.math.BigInteger;

/**
 * The test class for MyBigInteger.
 *
 * @author Zachary Kurmas
 * <p>
 * Split tests up so "easy" tests always run first.  For example,
 * test toString on "1" and "1234" before testing on "0".
 */
public class MyBigIntegerTest {

  private static final int ARRAY_SIZE = MyBigInteger.ARRAY_SIZE;

  private static java.math.BigInteger[] intsToTest = {
      new java.math.BigInteger("0"),
      new java.math.BigInteger("1"),
      new java.math.BigInteger("2"),
      new java.math.BigInteger("8"),
      new java.math.BigInteger("9"),
      new java.math.BigInteger("10"),
      new java.math.BigInteger("11"),
      new java.math.BigInteger("12"),
      new java.math.BigInteger("21"),
      new java.math.BigInteger("99"),
      new java.math.BigInteger("1000000"),
      new java.math.BigInteger("123456789"),
      new java.math.BigInteger("10203040506070809"),
      new java.math.BigInteger("111111111111"),
      new java.math.BigInteger("222222222222"),
  };

  private static MyBigInteger[] myBigInts;

  @BeforeClass
  public static void createTestInts() {
    myBigInts = new MyBigInteger[intsToTest.length];
    for (int x = 0; x < myBigInts.length; x++) {
      myBigInts[x] = new MyBigInteger(intsToTest[x].toString());
    }
  }

  @Test
  public void createsZero() {
    MyBigInteger zero = new MyBigInteger("0");
    Assert.assertEquals(1, zero.toString().length());
    Assert.assertEquals('0', zero.toString().charAt(0));
  }

  @Test
  public void createsSix() {
    MyBigInteger six = new MyBigInteger("6");
    Assert.assertEquals(1, six.toString().length());
    Assert.assertEquals('6', six.toString().charAt(0));
  }

  @Test
  public void createsOneTwoThree() {
    MyBigInteger test = new MyBigInteger("123");
    Assert.assertEquals(3, test.toString().length());
    Assert.assertEquals('1', test.toString().charAt(0));
    Assert.assertEquals('2', test.toString().charAt(1));
    Assert.assertEquals('3', test.toString().charAt(2));
  }

  @Test
  public void constructorHandlesLeadingZeros() {
    MyBigInteger test = new MyBigInteger("0000000000000000123");
    Assert.assertEquals(3, test.toString().length());
    Assert.assertEquals('1', test.toString().charAt(0));
    Assert.assertEquals('2', test.toString().charAt(1));
    Assert.assertEquals('3', test.toString().charAt(2));
  }


  @Test
  public void testConstructorAndToString() {
    // Tests the constructor using all of the integers above.
    for (int i = 0; i < intsToTest.length; i++) {
      MyBigInteger test = myBigInts[i];
      String expected = intsToTest[i].toString();
      Assert.assertEquals("Length of " + expected + " incorrect.",
          expected.toString().length(),
          test.toString().length());
      for (int j = 0; j < expected.length(); ++j) {
        Assert.assertEquals("Constructor/toString of " + expected + " differs at position " + j,
            expected.charAt(j), test.toString().charAt(j));
      }
    }
  }

  @Test
  public void equalsRecognizesEqual() {
    MyBigInteger i1 = new MyBigInteger("8675309");
    MyBigInteger i2 = new MyBigInteger("8675309");
    Assert.assertTrue(MyBigInteger.equals(i1, i2));
  }

  @Test
  public void equalsRecognizesEqualZero() {
    MyBigInteger i1 = new MyBigInteger("0");
    MyBigInteger i2 = new MyBigInteger("0");
    Assert.assertTrue(MyBigInteger.equals(i1, i2));
  }

  @Test
  public void equalsRecognizesNotEqual() {
    MyBigInteger i1 = new MyBigInteger("8675309");
    MyBigInteger i2 = new MyBigInteger("8675509");
    Assert.assertFalse(MyBigInteger.equals(i1, i2));
  }

  @Test
  public void testEqualsForAll() {
    for (int x = 0; x < intsToTest.length; x++) {
      for (int y = 0; y < intsToTest.length; y++) {
        boolean observed = MyBigInteger.equals(myBigInts[x], myBigInts[y]);
        boolean expected = intsToTest[x].equals(intsToTest[y]);

        Assert.assertEquals("Equals failed comparing " +
                intsToTest[x] + " and " + intsToTest[y],
            expected, observed);
      } // end for y
    } // end for x

  } // end testEquals

  @Test
  public void lessThanZeroZero() {
    MyBigInteger zero1 = new MyBigInteger("0");
    MyBigInteger zero2 = new MyBigInteger("0");
    Assert.assertFalse(MyBigInteger.lessThan(zero1, zero2));
  }

  @Test
  public void lessThanSameZero() {
    MyBigInteger zero1 = new MyBigInteger("0");
    Assert.assertFalse(MyBigInteger.lessThan(zero1, zero1));
  }

  @Test
  public void lessThanOneZero() {
    MyBigInteger zero1 = new MyBigInteger("1");
    MyBigInteger zero2 = new MyBigInteger("0");
    Assert.assertFalse(MyBigInteger.lessThan(zero1, zero2));
  }

  @Test
  public void lessThanZeroOne() {
    MyBigInteger zero1 = new MyBigInteger("0");
    MyBigInteger zero2 = new MyBigInteger("1");
    Assert.assertTrue(MyBigInteger.lessThan(zero1, zero2));
  }

  @Test
  public void lessThanDifferentLength() {
    MyBigInteger zero1 = new MyBigInteger("123");
    MyBigInteger zero2 = new MyBigInteger("98");
    Assert.assertFalse(MyBigInteger.lessThan(zero1, zero2));
    Assert.assertTrue(MyBigInteger.lessThan(zero2, zero1));
  }

  @Test
  public void testAllLessThan() {
    for (int x = 0; x < intsToTest.length; x++) {
      for (int y = 0; y < intsToTest.length; y++) {
        boolean observed = MyBigInteger.lessThan(myBigInts[x], myBigInts[y]);
        boolean expected = intsToTest[x].compareTo(intsToTest[y]) < 0;

        Assert.assertEquals("lessThan failed comparing " +
                intsToTest[x] + " and " + intsToTest[y],
            expected, observed);
      } // end for y
    } // end for x

  } // end test


  @Test
  public void addZeroZero() {
    MyBigInteger zero1 = new MyBigInteger("0");
    MyBigInteger zero2 = new MyBigInteger("0");
    Assert.assertEquals("0", MyBigInteger.add(zero1, zero2).toString());
    Assert.assertEquals("0", MyBigInteger.add(zero2, zero1).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("0", zero1.toString());
    Assert.assertEquals("0", zero2.toString());
  }

  @Test
  public void addZeroOne() {
    MyBigInteger zero1 = new MyBigInteger("0");
    MyBigInteger zero2 = new MyBigInteger("1");
    Assert.assertEquals("1", MyBigInteger.add(zero1, zero2).toString());
    Assert.assertEquals("1", MyBigInteger.add(zero2, zero1).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("0", zero1.toString());
    Assert.assertEquals("1", zero2.toString());
  }

  @Test
  public void add99One() {
    MyBigInteger zero1 = new MyBigInteger("99");
    MyBigInteger zero2 = new MyBigInteger("1");
    Assert.assertEquals("100", MyBigInteger.add(zero1, zero2).toString());
    Assert.assertEquals("100", MyBigInteger.add(zero2, zero1).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("99", zero1.toString());
    Assert.assertEquals("1", zero2.toString());
  }


  protected void addHelper(int a, int b) {
    java.math.BigInteger expected = intsToTest[a].add(intsToTest[b]);
    MyBigInteger observed = MyBigInteger.add(myBigInts[a], myBigInts[b]);
    Assert.assertEquals("Failure adding " + a + " and " + b,
        expected.toString(), observed.toString());
  }

  @Test
  public void testAllAdd() {
    for (int x = 0; x < intsToTest.length; x++) {
      for (int y = 0; y < intsToTest.length; y++) {
        addHelper(x, y);
      }
    }
  }

  @Ignore @Test
  public void multiplyZeroZero() {
    MyBigInteger zero1 = new MyBigInteger("0");
    MyBigInteger zero2 = new MyBigInteger("0");
    Assert.assertEquals("0", MyBigInteger.multiply(zero1, zero2).toString());
    Assert.assertEquals("0", MyBigInteger.multiply(zero2, zero1).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("0", zero1.toString());
    Assert.assertEquals("0", zero2.toString());
  }

  @Ignore @Test
  public void multiplyTenZero() {
    MyBigInteger zero1 = new MyBigInteger("10");
    MyBigInteger zero2 = new MyBigInteger("0");
    Assert.assertEquals("0", MyBigInteger.multiply(zero1, zero2).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("zero1 changed: ", "10", zero1.toString());
    Assert.assertEquals("zero2 changed", "0", zero2.toString());
  }

  @Ignore @Test
  public void multiplyByFive() {
    MyBigInteger zero1 = new MyBigInteger("473");
    MyBigInteger zero2 = new MyBigInteger("5");
    Assert.assertEquals("2365", MyBigInteger.multiply(zero1, zero2).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("zero1 changed: ", "473", zero1.toString());
    Assert.assertEquals("zero2 changed", "5", zero2.toString());
  }

  @Ignore @Test
  public void multiplyByTwentyFive() {
    MyBigInteger zero1 = new MyBigInteger("473");
    MyBigInteger zero2 = new MyBigInteger("25");
    Assert.assertEquals("11825", MyBigInteger.multiply(zero1, zero2).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("zero1 changed: ", "473", zero1.toString());
    Assert.assertEquals("zero2 changed", "25", zero2.toString());
  }


  @Ignore @Test
  public void multiplyZero10() {
    MyBigInteger zero1 = new MyBigInteger("0");
    MyBigInteger zero2 = new MyBigInteger("10");
    Assert.assertEquals("0", MyBigInteger.multiply(zero1, zero2).toString());
    Assert.assertEquals("0", MyBigInteger.multiply(zero2, zero1).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("zero1 changed: ", "0", zero1.toString());
    Assert.assertEquals("zero2 changed", "10", zero2.toString());
  }


  @Ignore @Test
  public void multiplyFiveFifteen() {
    MyBigInteger zero1 = new MyBigInteger("5");
    MyBigInteger zero2 = new MyBigInteger("15");
    Assert.assertEquals("75", MyBigInteger.multiply(zero1, zero2).toString());
    Assert.assertEquals("75", MyBigInteger.multiply(zero2, zero1).toString());

    // Make sure add method didn't mutate inputs
    Assert.assertEquals("5", zero1.toString());
    Assert.assertEquals("15", zero2.toString());
  }


  void multHelper(int a, int b) {
    java.math.BigInteger expected = intsToTest[a].multiply(intsToTest[b]);
    MyBigInteger observed = MyBigInteger.multiply(myBigInts[a], myBigInts[b]);
    Assert.assertEquals("Failure multiplying " + a + " and " + b,
        expected.toString(), observed.toString());
  }

  @Ignore @Test
  public void testAllMultiply() {
    for (int x = 0; x < intsToTest.length; x++) {
      for (int y = 0; y < intsToTest.length; y++) {
        multHelper(x, y);
      }
    }
  }

}
