import org.junit.*;

import static edu.gvsu.mipsunit.munit.MUnit.Register.*;
import static edu.gvsu.mipsunit.munit.MUnit.*;

public class InRangeTest {

  @Test
  public void verify_true_in_range() {
    run("in_range", 10, 20, 15);
    Assert.assertEquals(1, get(v0));
  }

  @Test
  public void verify_false_out_range() {
    run("in_range", 10, 20, 25);
    Assert.assertEquals(0, get(v0));
  }

  @Test
  public void verify_true_at_min() {
    run("in_range", 10, 20, 10);
    Assert.assertEquals(1, get(v0));
  }

  @Test
  public void verify_true_at_max() {
    run("in_range", 10, 20, 20);
    Assert.assertEquals(1, get(v0));
  }

  @Test
  public void does_not_modify_s_regs() {
    set(s0, 1343);
    set(s1, 1557);
    run("in_range", 10, 20, 15);
    Assert.assertEquals(1343, get(s0));
    Assert.assertEquals(1557, get(s1));
  }
}
