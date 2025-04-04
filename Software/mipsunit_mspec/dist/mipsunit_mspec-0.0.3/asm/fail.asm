################################################################################
#
# fail (test_name, register_label, expected_value, observed_value)
#
# Called when a test fails.
# prints a fail message and exits
# "Test #{$a0} failed.  Register #{$a1} expected to be #{$a2}; but was #{$a3}."
#
################################################################################

.data 

s_test: .asciiz "Test \\""
s_fail: .asciiz "\\" failed. "
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