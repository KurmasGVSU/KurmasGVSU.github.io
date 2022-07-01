import org.junit.*;
import org.junit.rules.Timeout;
import java.util.concurrent.TimeUnit;

import static edu.gvsu.mipsunit.munit.MUnit.Register.*;
import static edu.gvsu.mipsunit.munit.MUnit.*;

public class NcKTest {

    @Rule
public Timeout globalTimeout= new Timeout(10, TimeUnit.SECONDS);

    public int nCk(int n, int k) {
    if (k == 0 || n == k) {
      return 1;
    }
    if (k == 1) {
      return n;
    }

    return nCk(n - 1, k - 1) + nCk(n - 1, k);
  }


  /******************************************************************
   *
   * Test nCk
   *
   *****************************************************************/
  @Test
  public void test_5choose3() {
    run("nCk", 5, 3);
    Assert.assertEquals(10, get(v0));
  }

  // Write more tests.  Don't forget the edge cases.

}
