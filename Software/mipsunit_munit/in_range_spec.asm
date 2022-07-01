#
# This file automatically generated using MIPSUnit::MSpec
#

.globl main

.text
main:

#############################################
#
# in_range 
#
#############################################

#
# in_range returns true if value is in range
#
li $a0, 10
li $a1, 20
li $a2, 15
jal in_range
li $at, 1
beq $at, $v0, pass__1
move $a3, $v0
la $a0, test_name__1__1
la $a1, reg_name__1__1
li $a2, 1
jal fail
pass__1:

#
# in_range returns false if value is outside range
#
li $a0, 10
li $a1, 20
li $a2, 25
jal in_range
li $at, 0
beq $at, $v0, pass__2
move $a3, $v0
la $a0, test_name__2__1
la $a1, reg_name__2__1
li $a2, 0
jal fail
pass__2:

#
# in_range returns true if value is equal to minimum
#
li $a0, 10
li $a1, 20
li $a2, 10
jal in_range
li $at, 1
beq $at, $v0, pass__3
move $a3, $v0
la $a0, test_name__3__1
la $a1, reg_name__3__1
li $a2, 1
jal fail
pass__3:

#
# in_range returns true if value is equal to maximum
#
li $a0, 10
li $a1, 20
li $a2, 20
jal in_range
li $at, 1
beq $at, $v0, pass__4
move $a3, $v0
la $a0, test_name__4__1
la $a1, reg_name__4__1
li $a2, 1
jal fail
pass__4:

#
# end of tests
#

.data
s_pass: .asciiz "All tests pass."

.text
la $a0, s_pass
li $v0, 4
syscall

li $v0, 10
syscall
################################################################################
#
# fail (test_name, register_label, expected_value, observed_value)
#
# Called when a test fails.
# prints a fail message and exits
# "Test  failed.  Register  expected to be ; but was ."
#
################################################################################

.data 

s_test: .asciiz "Test \""
s_fail: .asciiz "\" failed. "
s_expected: .asciiz " expected to be "
s_observed: .asciiz "; but was "
s_period: .asciiz "."

r: .ascii "v0"

.text	
fail:				

# Save name parameter in $t8
move $t8, $a0	

# Print "Test "
li $v0, 4
la $a0, s_test
syscall 

# Print the name of the test
move $a0, $t8
syscall

# Print " failed. "
la $a0, s_fail
syscall

# print name of register
move $a0, $a1
syscall

# Print " expected to be "
la $a0, s_expected
syscall

# print expected value
li $v0, 1
move $a0, $a2
syscall

# print "; but was "
li $v0, 4
la $a0, s_observed
syscall

# print observed value
li $v0, 1
move $a0, $a3
syscall

# print "."
li $v0, 4
la $a0, s_period
syscall
	
# exit
li $v0, 10 
syscall

.data
# Data Labels:
test_name__1__1: .asciiz "in_range returns true if value is in range"
reg_name__1__1: .asciiz "Register v0"
test_name__2__1: .asciiz "in_range returns false if value is outside range"
reg_name__2__1: .asciiz "Register v0"
test_name__3__1: .asciiz "in_range returns true if value is equal to minimum"
reg_name__3__1: .asciiz "Register v0"
test_name__4__1: .asciiz "in_range returns true if value is equal to maximum"
reg_name__4__1: .asciiz "Register v0"
