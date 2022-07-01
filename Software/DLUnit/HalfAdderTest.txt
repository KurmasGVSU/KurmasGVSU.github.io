import static edu.gvsu.dlunit.DLUnit.*;
import org.junit.Assert;
import org.junit.Test;

public class HalfAdderTest {

  @Test
  public void zero_zero() {
    setPin("InputA", false);
    setPin("InputB", false);
    run();
    Assert.assertEquals("Checking Sum", false, readPin("Sum"));
    Assert.assertEquals("Checking Carry", false, readPin("Carry"));
  }

  @Test
  public void zero_one() {
    setPin("InputA", false);
    setPin("InputB", true);
    run();
    Assert.assertEquals("Checking Sum", true, readPin("Sum"));
    Assert.assertEquals("Checking Carry", false, readPin("Carry"));
  }

  @Test
  public void one_zero() {
    setPin("InputA", true);
    setPin("InputB", false);
    run();
    Assert.assertEquals("Checking Sum", true, readPin("Sum"));
    Assert.assertEquals("Checking Carry", false, readPin("Carry"));
  }

  @Test
  public void one_one() {
    setPin("InputA", true);
    setPin("InputB", true);
    run();
    Assert.assertEquals("Checking Sum", false, readPin("Sum"));
    Assert.assertEquals("Checking Carry", true, readPin("Carry"));
  }
}
