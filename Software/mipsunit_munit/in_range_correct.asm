.globl in_range

#
# in_range(min, max, value)
# return true if min <= value <= max
# return false otherwise
#

in_range:
	blt $a2, $a0, false
	bgt $a2, $a1, false
	li $v0, 1
	jr $ra
false:
	li $v0, 0
	jr $ra
	