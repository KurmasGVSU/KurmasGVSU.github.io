#  exampleCIT-4.s
#  data output

	.data

	.text
	.globl main
main:	
	li $v0, 1		# syscall to print integer (value in $a0)
	syscall
	
	li $v0, 10		# syscall to exit
	syscall   		# execute syscall

