import java.util.*;
/****************************************************************
This class contains several methods with multiple
logical errors.  Although, it should compile with no errors.

@author Ana Posada
@author Scott Grissom
@version February 20, 2018
****************************************************************/
public class Debugging{
    /** quantity on hand for sale */
    private int inventory;
    
    /** constants used for convertion to farenheit temperatures */
    private final double RATIO = 9 / 5;
    private final int BASE = 32;

/******************************************************************
Default constructor should set inventory to -1
******************************************************************/  
    public Debugging(){
        inventory = 0;
    }
    
/******************************************************************
Alternative constructor should set inventory
@param inventory amount to be assigned to the instance field
******************************************************************/     
    public Debugging(int inventory){
        inventory = inventory;
    }

/******************************************************************
Set inventory
@param inventory amount to be assigned to the instance field
******************************************************************/      
    public void setInventory(int amount){
        amount = inventory;
    }
    
/******************************************************************
Get inventory - Assume no error for this method but is needed for
testing other methods.
@return the current inventory
******************************************************************/  
    public int getInventory(){
        return inventory;
    }
    
/******************************************************************
Calculate the 'base' raised to the 'exp' power

For example, power(2, 4) should return 16
@param base the number to be raised
@param exp the exponent
@return a new number that represents base raised to the exp power.
******************************************************************/  
    public int power(int base, int exp){
        int result = 0;
        for(int i=1; i<exp; i++){
            result = result * base;
        }
        return result;
    }
    
/****************************************************************
 * Convert a given temperature in celsius to fahrenheit.
 * @param celsius temperature to be converted
 * @return converted temperature in fahrenheit
****************************************************************/    
    public double toFahrenheit(double celsius){
        double f = celsius * RATIO + BASE;
        return f;
    }
    
 /**************************************************************
 * Description taken from codingbat.com
 * You are driving a little too fast, and a police officer stops you. 
 * Write the code to compute the result, encoded as an integer: 
 * 0 = no ticket, 1 = small ticket, 2 = big ticket.
 * If speed is 60 or less, the result is 0. 
 * If speed is between 61 and 80 inclusive, the result is 1. 
 * If speed is 81 or more, the result is 2. 
 * Unless it is your birthday -- on that day, 
 * your speed can be 5 higher in all cases. 
 * 
 * Exmples: 
 * caughtSpeeding(60, false) ? 0
 * caughtSpeeding(65, false) ? 1
 * caughtSpeeding(65, true) ? 0
 ***************************************************************/
    public int caughtSpeeding(int speed, boolean isBirthday)  {
        int over = 0;
        int ticket = -1;
        if (isBirthday = true ){
            over = 5;
        }    
        if (speed <= 60 + over){
            ticket = 0;
        }else if (speed  >= 61 + over && speed <= 80 + over){
            ticket = 1;
        }else{
            ticket = 2;
        }
        return ticket;
    }
    
/****************************************************************
Calculate an average given a single String with a sequence of
numbers terminated by a negative value.
@param nums a String or numbers ending in a negative
For example, "10 20 30 40 50 -1" 

@return the computed average of all positive numbers
****************************************************************/    
    public double calcAverage(String nums){
        int val, count = 0;
        double total = 0.0;
        Scanner scnr = new Scanner(nums);
        do{
           val = scnr.nextInt();
           if(val > 0)
               total = total + val;   
               count++;
        }while(val > 0);
        return total / count;
    }

/****************************************************************
Return a new String that has all occurence of character c removed.
For exmample removeChar("Help!!!!", '!') returns "Help"

@param str the initial String
@param c the character to be removed from str
@return the modified String

@return the computed average of all positive numbers
****************************************************************/     
    public String removeChar(String str, char c){
        String result = "";
        int index = 0;
        while(index < str.length()){ 
            if(str.charAt(index) != c){
                result = str + str.charAt(index);
            }
        }
        return result;
    }

}
