.globl reverse

.data

array1:	 .word 1 2 3 4 5 6 7 8 9 10
array2:	 .word 2 4 7 9 15 17 23 33

.text

la $a0, array1
li $a1, 6
jal reverse

li $v0, 10
syscall


#
# Reverse the array
#	
# a0: starting label
# a1: length
reverse:
sll $t0, $a1, 2 # multiply length by 4
add $t1, $a0, $t0 # point to spot past end of array

	# Ooops.   Now we're off by 1.#
	#addi $t1, $t1, -4 # point to last spot in the array


ble $t1, $a0, end
top:
# swap 
lw $t2, 0($a0)
lw $t3, 0($t1)
sw $t3, 0($a0)
sw $t2, 0($t1)

addi $a0, $a0, 4
addi $t1, $t1, -4

blt $a0, $t1, top

end:
jr $ra


	
