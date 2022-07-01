#include "unity.h"
#include "connect4.h"
#include "c4_test_helper.h"

void setUp(void) {
    // set stuff up here
}

void tearDown(void) {
    // clean stuff up here
}

/////////////////////////////////////////////
//
// Winner Tests
//
/////////////////////////////////////////////


/*
   SETUP2(6,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1, -1, -1, -1}};
}
*/


void win1a_no_win_empty_l4() {

  SETUP(6,7);

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT(C4_NO_WINNER_YET, observed);
}

void win1b_no_win_empty_l3() {

  SETUP(6,7);

  int observed = WINNER(3);
  TEST_ASSERT_EQUAL_INT(C4_NO_WINNER_YET, observed);
}

void win1c_no_win_empty_l7() {

  SETUP(6,7);

  int observed = WINNER(7);
  TEST_ASSERT_EQUAL_INT(C4_NO_WINNER_YET, observed);
}

void win1d_no_win_empty_small() {

  SETUP(3,3);

  int observed = WINNER(2);
  TEST_ASSERT_EQUAL_INT(C4_NO_WINNER_YET, observed);
}

void win1e_no_one_row_short() {

  SETUP(6,7);
  array[0][1] = 0;
  array[0][2] = 0;
  array[0][3] = 0;

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT(C4_NO_WINNER_YET, observed);
}

void win1e_no_one_row_split() {

  SETUP(6,7);
  array[0][1] = 0;
  array[0][2] = 0;
  array[0][4] = 0;
  array[0][5] = 0;

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT(C4_NO_WINNER_YET, observed);
}


//
// Wins in rows
//


void win2a_horizontal_row_0_length4() {

 // SETUP, WINNER are macros.
 // SETUP 
 //   (1) Declares variables named num_rows, num_columns, and array
 //   (2) Initializes array to be empty. 
 // WINNER is a short-cut for calling the winner function with the required parameters.
 // (See c4_test_helper.h)

  SETUP(6,7);

  array[0][0] = 0;
  array[0][1] = 0;
  array[0][2] = 0;
  array[0][3] = 0;

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win");
}

void win2b_horizontal_row_0_length3() {

  SETUP(6,7);

  array[0][1] = 0;
  array[0][2] = 0;
  array[0][3] = 0;

  int observed = WINNER(3);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 3 win");

  int observed2 = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 4 win");
}

void win2c_horizontal_row_0_length6() {

  SETUP(6,8);

  array[0][1] = 0;
  array[0][2] = 0;
  array[0][3] = 0;
  array[0][4] = 0;
  array[0][5] = 0;
  array[0][6] = 0;

  int observed = WINNER(6);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 6 win");

  int observed2 = WINNER(7);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 7 win");
}

void win2d_horizontal_row_0_length6_edge() {

  SETUP(6,7);

  array[0][1] = 0;
  array[0][2] = 0;
  array[0][3] = 0;
  array[0][4] = 0;
  array[0][5] = 0;
  array[0][6] = 0;

  int observed = WINNER(6);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 6 win");

  int observed2 = WINNER(7);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 7 win");
}

void win2e_horizontal_row_1_length4() {

  SETUP(6,7);

  array[0][0] = 0;
  array[0][1] = 1;
  array[0][2] = 0;
  array[0][3] = 1;
  array[0][4] = 0;
  array[0][5] = 1;
  array[0][6] = 0;

  array[1][1] = 1;
  array[1][2] = 1;
  array[1][3] = 1;
  array[1][4] = 1;

  array[1][0] = 0;
  array[1][5] = 0;
  array[1][6] = 0;

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win");
}

void win2f_horizontal_row_5_length4() {

  SETUP2(3, 8) =  
         {{-1, -1, 0, 0, 0, 0, -1},
          {-1, -1, 1, 1, 0, 1, -1},
          {1, -1, 0, 0, 1, 0, 1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win");
}


//
// Wins in columns
//


void win3a_vertical_column_0() {

  SETUP(6,7);

  array[1][0] = 0;
  array[2][0] = 0;
  array[3][0] = 0;
  array[4][0] = 0;
  array[5][0] = 1;

  array[5][2] = 1;
  array[5][4] = 1;
  array[4][4] = 1;

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win");  
}


void win3b_vertical_column_1() {

  SETUP(6,7);

  array[1][1] = 0;
  array[2][1] = 0;
  array[3][1] = 0;
  array[4][1] = 0;
  array[5][1] = 1;

  array[5][3] = 1;
  array[5][6] = 1;
  array[4][6] = 1;


  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}


void win3c_vertical_column_0_length5() {

  SETUP(6,7);

  array[1][0] = 0;
  array[2][0] = 0;
  array[3][0] = 0;
  array[4][0] = 0;
  array[5][0] = 0;

  array[5][2] = 1;
  array[5][4] = 1;
  array[4][4] = 1;
  array[4][2] = 1;

  int observed = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 5 win");

  int observed2 = WINNER(6);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 6 win"); 
}

void win3d_vertical_column_2_length2() {

  SETUP2(3,4) = {{0, -1, -1, -1}, {1, -1, -1, 1}, {0, -1, 0, 1}};

  int observed = WINNER(2);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 2 win");

  int observed2 = WINNER(3);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 3 win"); 
}

//
// Wins top down right
//
void win4a_top_down_right_middle1() {

  SETUP2(6,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  1,  1, -1, -1, -1, -1},
    {-1,  0,  0,  1, -1, -1, -1},
    {-1,  1,  1,  0,  1, -1, -1},
    { 1,  0,  0,  0,  1, -1, -1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

void win4b_top_down_right_middle2() {

  SETUP2(6,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1,  1, -1, -1, -1, -1},
    {-1, -1,  1,  1, -1, -1, -1},
    {-1, -1,  0,  0,  1, -1, -1},
    {-1, -1,  1,  1,  0,  1, -1},
    {-1,  1,  0,  0,  0,  1, -1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}


void win4c_top_down_right_bottom1() {

  SETUP2(5,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  1,  1, -1, -1, -1, -1},
    { 0,  0,  0,  1, -1, -1, 0},
    { 0,  1,  1,  0,  1, -1, 0}};

  int observed = WINNER(4);
   TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

void win4d_top_down_right_bottom2() {

  SETUP2(5,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1,  1, -1, -1, -1, -1},
    {-1, 0,  1,  1, -1, -1, -1},
    {-1, 0,  0,  0,  1, -1, -1},
    { 0, 0,  1,  1,  0,  1, -1}};

  int observed = WINNER(4);
   TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}



void win4e_top_down_right_top() {

  SETUP2(5,7) = {
    {-1,  1,  1, -1, -1, -1, -1},
    {-1,  0,  0,  1, -1, -1, -1},
    {-1,  1,  1,  0,  1, -1, -1},
    { 1,  0,  0,  0,  1,  1, -1},
    { 0,  1,  0,  1,  0,  0,  -1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

void win4f_top_down_right_to_corner_short() {

  SETUP2(3,5) = {
    {-1, -1, 0, -1, -1},
    {-1, -1, 1, 0,  0},
    {-1, -1, 1, 1,  0}};

  int observed = WINNER(3);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 3 win");

  int observed2 = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 4 win"); 
}

void win4g_top_down_right_main_diagonal3() {
   SETUP2(3,3) = {
    {1, -1, -1}, 
    {0, 1, -1},
    {0, 0, 1}};

  int observed = WINNER(3);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 3 win");

  int observed2 = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 4 win"); 
}

void win4h_top_down_right_main_diagonal5() {

  SETUP2(5,5) = {
    {1, -1, -1, -1, -1}, 
    {1, 1, -1,  -1, -1},
    {0, 0, 1,   -1, -1},
    {0, 1, 0,    1,  1},
    {0, 0, 1,    0,  1}};

  int observed = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 5 win");

  int observed2 = WINNER(6);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 6 win"); 
}

//
// Wins left down right
//
void win5a_left_down_right_left_length5() {

  SETUP2(6,7) = {
    {-1, -1, -1, -1, -1,  -1, -1},
    {1,  -1, -1, -1, -1,  -1, -1}, 
    {1,   1, -1, -1. -1,  -1, -1},
    {0,   0,  1, -1, -1,  -1, -1},
    {0,   1,  0,  1,  1,  -1, -1},
    {0,   0,  1,  0,  1,  -1, -1}};

  int observed = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 5 win");

  int observed2 = WINNER(6);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 6 win"); 
}

void win5b_left_down_right_from_left_length4() {

  SETUP2(6,6) = {
    {-1, -1, -1, -1, -1, -1},
    { 1, -1, -1, -1, -1, -1},
    { 1,  1, -1, -1, -1, -1},
    { 0,  0,  1, -1, -1, -1},
    { 1,  1,  0,  1, -1, -1},
    { 0,  0,  0,  1, -1, -1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}


void win5c_left_down_right_to_corner_tall() {

  SETUP2(5,3) = {
    {-1, -1, -1},
    {-1, -1, -1},
    {0, -1, -1},
    {1, 0,  0},
    {1, 1,  0}};

  int observed = WINNER(3);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 3 win");

  int observed2 = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 4 win"); 
}

//
// Wins bottom up right
//

void win6a_bottom_up_middle() {

 SETUP2(6,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1,  0, -1, -1},
    {-1, -1, -1,  0,  0, -1, -1},
    {-1, -1,  0,  1,  1, -1, -1},
    {-1,  0,  1,  0,  0, -1, -1},
    {-1,  1,  0,  1,  1, -1,  1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

void win6b_bottom_up_bottom() {

 SETUP2(6,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1,  -1, -1, -1},
    {-1, -1, -1,  0,  1, -1, -1},
    {-1, -1,  0,  1,  1, -1, -1},
    {-1,  0,  1,  0,  0, -1, -1},
    {-1,  1,  0,  1,  1, -1,  1}};

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

void win6c_bottom_up_top() {

 SETUP2(6,8) = {   
    {-1, -1, -1, -1, -1, -1,  0, -1},
    {-1, -1, -1, -1, -1,  0,  0, -1},
    {-1, -1, -1, -1,  0,  1,  1, -1},
    {-1, -1, -1,  0,  1,  0,  0, -1},
    {-1,  1, -1,  1,  0,  1,  1, -1},
    { 1,  0,  1,  0,  0,  1,  0, -1},
    };

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}


void win6d_bottom_up_right() {

 SETUP2(7,7) = {   
    {-1, -1, -1, -1, -1, -1, -1},
    {-1, -1, -1, -1, -1, -1,  0},
    {-1, -1, -1, -1, -1,  0,  0},
    {-1, -1, -1, -1,  0,  1,  1},
    {-1, -1, -1,  0,  1,  0,  0},
    {-1,  1, -1,  1,  0,  1,  1},
    { 1,  0,  1,  0,  0,  1,  0},
    };

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

//
// Wins bottom up right
//

void win7a_left_up_top() {

 SETUP2(6,7) = {   
    {-1, -1, -1, -1,  0, -1, -1},
    {-1, -1, -1,  0,  0, -1, -1},
    {-1, -1,  0,  1,  1, -1, -1},
    {-1,  0,  1,  0,  0, -1, -1},
    {-1,  1,  0,  1,  1, -1,  1},
    { 0,  0,  0,  1,  0,  1,  1},
    };

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}


void win7b_left_up_middle() {

 SETUP2(7,7) = {   
    {-1, -1, -1, -1, -1, -1, 1},
    {-1, -1, -1, -1,  0, -1, -1},
    {-1, -1, -1,  0,  0, -1, -1},
    {-1, -1,  0,  1,  1, -1, -1},
    {-1,  0,  1,  0,  0, -1, -1},
    {-1,  1,  0,  1,  1, -1,  1},
    { 0,  0,  0,  1,  0,  1,  1},
    };

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}

void win7c_left_up_left() {

 SETUP2(7,6) = {   
    {-1, -1, -1, -1, -1, 1},
    {-1, -1, -1,  0, -1, -1},
    {-1, -1,  0,  0, -1, -1},
    {-1,  0,  1,  1, -1, -1},
    { 0,  1,  0,  0, -1, -1},
    { 1,  0,  1,  1, -1,  1},
    { 0,  0,  1,  0,  1,  0},
    };

  int observed = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(0, observed, "Check for length 4 win");

  int observed2 = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 5 win"); 
}


void win7d_bottom_up_main_diagonal3() {
   SETUP2(3,3) = {
    {-1, -1, 1}, 
    {-1,  1, 0},
    { 1,  0, 0}};

  int observed = WINNER(3);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 3 win");

  int observed2 = WINNER(4);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 4 win"); 
}

void win7e_bottom_up_main_diagonal5() {

  SETUP2(5,5) = {
    {-1, -1, -1, -1, 1}, 
    {-1, -1, -1,  1, 1},
    {-1, -1,  1,  0, 0},
    { 1,  1,  0,  1, 0},
    { 1,  0,  1,  0, 0}};

  int observed = WINNER(5);
  TEST_ASSERT_EQUAL_INT_MESSAGE(1, observed, "Check for length 5 win");

  int observed2 = WINNER(6);
  TEST_ASSERT_EQUAL_INT_MESSAGE(C4_NO_WINNER_YET, observed2, "Check for length 6 win"); 
}



/////////////////////////////////////////////
//
// Placement Tests
//
/////////////////////////////////////////////

void place1a_first_token_in_column0() {

  SETUP(6,7)
  COPY_ARRAY  // creates an identical array named expected
  int observed_row = PLACE_TOKEN(0, 0)
  TEST_ASSERT_EQUAL_INT(5, observed_row);

  expected[5][0] = 0;
  
  ASSERT_ARRAY_EQUAL(expected, array)
}

void place1b_first_token_in_column1() {

  SETUP(4,7)
  COPY_ARRAY  // creates an identical array named expected
  int observed_row = PLACE_TOKEN(0, 1)
  TEST_ASSERT_EQUAL_INT(3, observed_row);

  expected[3][1] = 0;
  
  ASSERT_ARRAY_EQUAL(expected, array)
}

void place1b_first_token_in_last_column() {

  SETUP(6,9)
  COPY_ARRAY  // creates an identical array named expected
  int observed_row = PLACE_TOKEN(0, 8)
  TEST_ASSERT_EQUAL_INT(5, observed_row);

  expected[5][8] = 0;
  
  ASSERT_ARRAY_EQUAL(expected, array)
}

void place2a_second_token_in_column() {

  SETUP(6,7)
  COPY_ARRAY  // creates an identical array named expected
  PLACE_TOKEN(0, 3)
  int observed_row = PLACE_TOKEN(1, 3)
  TEST_ASSERT_EQUAL_INT(4, observed_row);

  expected[5][3] = 0;
  expected[4][3] = 1;
  
  ASSERT_ARRAY_EQUAL(expected, array)
}

void place3a_fill_top() {

 SETUP2(6,7) = {
    {-1, -1, -1, -1, -1, -1, -1},
    {-1,  0, -1, -1, -1, -1, -1},
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  0, -1, -1, -1, -1, -1},
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  0, -1, -1,  1, -1, -1}};

 COPY_ARRAY  // creates an identical array named expected
 int observed_row = PLACE_TOKEN(1, 1)
  TEST_ASSERT_EQUAL_INT(0, observed_row);

 expected[0][1] = 1;

  ASSERT_ARRAY_EQUAL(expected, array)
}



void place4a_attempt_to_overfill() {

 SETUP2(6,7) = {
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  0, -1, -1, -1, -1, -1},
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  0, -1, -1, -1, -1, -1},
    {-1,  1, -1, -1, -1, -1, -1},
    {-1,  0, -1, -1,  1, -1, -1}};

 COPY_ARRAY  // creates an identical array named expected
 int observed_row = PLACE_TOKEN(0, 1)
  TEST_ASSERT_EQUAL_INT(-1, observed_row);

  ASSERT_ARRAY_EQUAL(expected, array)
}



/////////////////////////////////////////////
//
// Main
//
/////////////////////////////////////////////


int main(void) {

    UNITY_BEGIN();
RUN_TEST(win1a_no_win_empty_l4);
RUN_TEST(win1b_no_win_empty_l3);
RUN_TEST(win1c_no_win_empty_l7);
RUN_TEST(win1d_no_win_empty_small);
RUN_TEST(win1e_no_one_row_short);
RUN_TEST(win1e_no_one_row_split);
RUN_TEST(win2a_horizontal_row_0_length4);
RUN_TEST(win2b_horizontal_row_0_length3);
RUN_TEST(win2c_horizontal_row_0_length6);
RUN_TEST(win2d_horizontal_row_0_length6_edge);
RUN_TEST(win2e_horizontal_row_1_length4);
RUN_TEST(win2f_horizontal_row_5_length4);
RUN_TEST(win3a_vertical_column_0);
RUN_TEST(win3b_vertical_column_1);
RUN_TEST(win3c_vertical_column_0_length5);
RUN_TEST(win3d_vertical_column_2_length2);
RUN_TEST(win4a_top_down_right_middle1);
RUN_TEST(win4b_top_down_right_middle2);
RUN_TEST(win4c_top_down_right_bottom1);
RUN_TEST(win4d_top_down_right_bottom2);
RUN_TEST(win4e_top_down_right_top);
RUN_TEST(win4f_top_down_right_to_corner_short);
RUN_TEST(win4g_top_down_right_main_diagonal3);
RUN_TEST(win4h_top_down_right_main_diagonal5);
RUN_TEST(win5a_left_down_right_left_length5);
RUN_TEST(win5b_left_down_right_from_left_length4);
RUN_TEST(win5c_left_down_right_to_corner_tall);
RUN_TEST(win6a_bottom_up_middle);
RUN_TEST(win6b_bottom_up_bottom);
RUN_TEST(win6c_bottom_up_top);
RUN_TEST(win6d_bottom_up_right);
RUN_TEST(win7a_left_up_top);
RUN_TEST(win7b_left_up_middle);
RUN_TEST(win7c_left_up_left);
RUN_TEST(win7d_bottom_up_main_diagonal3);
RUN_TEST(win7e_bottom_up_main_diagonal5);


RUN_TEST(place1a_first_token_in_column0);
RUN_TEST(place1b_first_token_in_column1);
RUN_TEST(place1b_first_token_in_last_column);
RUN_TEST(place2a_second_token_in_column);
RUN_TEST(place3a_fill_top);
RUN_TEST(place4a_attempt_to_overfill);
    return UNITY_END();
}