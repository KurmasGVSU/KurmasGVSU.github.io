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