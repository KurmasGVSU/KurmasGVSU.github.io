import org.junit.*;

/**
 * Unit tests for SimpleTime
 *
 * @author Zachary Kurmas
 */
public class SimpleTimeTest
{
   
    @Test
    public void defaultConstructorSetsToMidnight() 
    {
        SimpleTime time = new SimpleTime();
        Assert.assertTrue(time.isMidnight());
    }
    
    @Test
    public void isMidnightRecognizesMidnight()
    {
        SimpleTime time = new SimpleTime(0,0,0);
        Assert.assertTrue(time.isMidnight());
    }
    
    @Test
    public void isMidnightRecognizesNotMidnight()
    {
       SimpleTime time1 = new SimpleTime(0, 0, 1);
       Assert.assertFalse("12:00:01 should be false", time1.isMidnight());
       
       SimpleTime time2 = new SimpleTime(23, 59, 59);
       Assert.assertFalse("23:59:59 should be false", time2.isMidnight());
        
       SimpleTime time3 = new SimpleTime(12, 0, 0);
       Assert.assertFalse("12:00:00 should be false", time3.isMidnight());
       
       SimpleTime time4 = new SimpleTime(0, 1, 0);
       Assert.assertFalse("0:01:00 should be false", time4.isMidnight());
    }
    
    @Test
    public void isNoonRecognizesNoon()
    {
        SimpleTime time = new SimpleTime(12,0,0);
        Assert.assertTrue(time.isNoon());
    }
    
    @Test
    public void isNoonRecognizesNotNoon()
    {
       SimpleTime time1 = new SimpleTime(12, 0, 1);
       Assert.assertFalse("12:00:01 should be false", time1.isNoon());
       
       SimpleTime time2 = new SimpleTime(11, 59, 59);
       Assert.assertFalse("11:59:59 should be false", time2.isNoon());
        
       SimpleTime time3 = new SimpleTime(0, 0, 0);
       Assert.assertFalse("0:00:00 should be false", time3.isNoon());
       
       SimpleTime time4 = new SimpleTime(12, 1, 0);
       Assert.assertFalse("12:01:00 should be false", time4.isNoon());
    }
    
    
    @Test
    public void isLunchTimeRecognizesNoon()
    {
        SimpleTime time = new SimpleTime(12,0,0);
        Assert.assertTrue(time.isLunchTime());
    }
    
    @Test
    public void isLunchTimeRecognizesOneOClock()
    {
        SimpleTime time = new SimpleTime(13, 0 ,0);
        Assert.assertTrue(time.isLunchTime());
    }
    
    @Test
    public void isLunchTimeRecognizesNoonHour()
    {
        SimpleTime time = new SimpleTime(12, 0 ,1);
        Assert.assertTrue("12:00:01", time.isLunchTime());
        
        SimpleTime time2 = new SimpleTime(12, 1 ,0);
        Assert.assertTrue("12:01:00", time2.isLunchTime());
        
        SimpleTime time3 = new SimpleTime(12, 30 ,0);
        Assert.assertTrue("12:30:00", time3.isLunchTime());
        
        SimpleTime time4 = new SimpleTime(12, 59 ,0);
        Assert.assertTrue("12:59:00", time4.isLunchTime());
        
        SimpleTime time5 = new SimpleTime(12, 59 ,59);
        Assert.assertTrue("12:59:59", time5.isLunchTime());
    }
    
    @Test
    public void isLunchTimeNotAcceptRestofOneOClock() 
    {
        SimpleTime time = new SimpleTime(13, 0 ,1);
        Assert.assertFalse("13:00:01", time.isLunchTime());
        
        SimpleTime time2 = new SimpleTime(13, 1 ,0);
        Assert.assertFalse("13:01:00", time2.isLunchTime());
        
        SimpleTime time3 = new SimpleTime(13, 59 ,0);
        Assert.assertFalse("13:59:00", time3.isLunchTime());        
    }
    
    @Test
    public void isLunchTimeNotAcceptOtherHours() 
    {
        SimpleTime time = new SimpleTime(11, 59 ,59);
        Assert.assertFalse("11:59:59", time.isLunchTime());
        
        SimpleTime time2 = new SimpleTime(14, 0 ,0);
        Assert.assertFalse("14:00:00", time2.isLunchTime());
        
        SimpleTime time3 = new SimpleTime(0, 0 ,0);
        Assert.assertFalse("0:00:00", time3.isLunchTime());        
    }
    
    @Test
    public void toStringDoubleDigits()
    {
        SimpleTime time = new SimpleTime(10, 30, 45);
        Assert.assertEquals("10:30:45", time.toString());
    }
    
    @Test
    public void toStringSingleDigits()
    {
        SimpleTime time = new SimpleTime(3, 7, 9);
        Assert.assertEquals("3:07:09", time.toString());
    }
    
    @Test
    public void toStringMidnight()
    {
        SimpleTime time = new SimpleTime(0,0, 0);
        Assert.assertEquals("0:00:00", time.toString());
    }
    
    @Test
    public void toStringMixed()
    {
        SimpleTime time = new SimpleTime(7,10, 5);
        Assert.assertEquals("7:10:05", time.toString());
        
        SimpleTime time2 = new SimpleTime(10,6, 36);
        Assert.assertEquals("10:06:36", time2.toString());
    }
    
    @Test
    public void incrementMidnight() 
    {
       SimpleTime time = new SimpleTime(0, 0, 0);
       time.increment();
       Assert.assertEquals("0:00:01", time.toString());        
    }
    
    @Test
    public void incrementMinuteRollover() 
    {
       SimpleTime time = new SimpleTime(3, 17, 59);
       time.increment();
       Assert.assertEquals("3:18:00", time.toString());        
    }
    
    @Test
    public void incrementHourRollover() 
    {
       SimpleTime time = new SimpleTime(5, 59, 59);
       time.increment();
       Assert.assertEquals("6:00:00", time.toString());        
    }
    
    @Test
    public void incrementDayRollover() 
    {
       SimpleTime time = new SimpleTime(23, 59, 59);
       time.increment();
       Assert.assertEquals("0:00:00", time.toString());        
    }
    
    @Test
    public void multipleIncrement() 
    {
       SimpleTime time = new SimpleTime(10, 58, 50);
       
       for (int i = 0; i < 137; i++) {
          time.increment();
       }
       Assert.assertEquals("11:01:07", time.toString());        
    }
    
    
}

