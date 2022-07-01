import org.junit.*;

/**
 * Write a description of class DebuggingTest here.
 *
 * @author (your name)
 * @version (a version number or a date)
 */
public class DebuggingTest
{
    @Test
    public void testCalcAverage() {
        Debugging bug1 = new Debugging();
        Assert.assertEquals(20.0, bug1.calcAverage("10 20 30 -100"), 0.0001);
    }    
     
    @Test
    public void testToFarenheit() {
     
        Debugging bug1 = new Debugging();
        Assert.assertEquals(212.0, bug1.toFahrenheit(100.0), 0.0001);
    }
    
}
