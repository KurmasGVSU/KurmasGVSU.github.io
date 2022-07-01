#  sizes.s
#  data storage

	.data
val:	.word 42		# data variable and value
val2:	.word 1
val4:	.byte 257
	
	.text
	.globl main
main:	
	li $v0, 10		# syscall to exit
	syscall   		# execute syscall

