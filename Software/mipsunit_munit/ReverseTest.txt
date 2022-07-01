import org.junit.*;

import edu.gvsu.mipsunit.munit.MUnit;;
import static edu.gvsu.mipsunit.munit.MUnit.*;

public class ReverseTest {

    @Test
    public void reversePartialList() {
        MUnit.Label array1 = wordData(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        run("reverse", array1, 6);
        int[] expected = {6, 5, 4, 3, 2, 1, 7};
        int[] observed = getWords(array1, 0, 7);
        Assert.assertArrayEquals(expected, observed);
        Assert.assertTrue(noOtherMemoryModifications());
    }

    @Test
    public void verifyThatThisFails() {
        MUnit.Label array1 = wordData(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        run("reverse", array1, 6);
        Assert.assertArrayEquals(new int[]{6, 5, 4, 3, 2}, getWords(array1, 0, 5));
        // This should fail because position 5 was modified, but not checked.
        Assert.assertFalse("This should be false!", noOtherMemoryModifications());
    }
}