	.file	"timestamp_shell.c"
	.text
	.section	.rodata
	.align 8
.LC0:
	.string	"%8d; Start %10u; Stop %10u; Difference %5d\n"
	.text
	.globl	main
	.type	main, @function
main:
.LFB0:
	.cfi_startproc
	pushq	%rbp
	.cfi_def_cfa_offset 16
	.cfi_offset 6, -16
	movq	%rsp, %rbp
	.cfi_def_cfa_register 6
	pushq	%r13
	pushq	%r12
	pushq	%rbx
	subq	$8, %rsp
	.cfi_offset 13, -24
	.cfi_offset 12, -32
	.cfi_offset 3, -40
	movl	$100, %r12d
	movl	$200, %r13d
	movl	$-1, %r8d
	movl	$0, %r8d
	jmp	.L2
	.L3:
	mov $0, %eax
	cpuid
	rdtsc
	movl    %eax, %r12d

	#@    addl $1, %r9d
	#@ addl $1, %eax
	#@ addl $1, %r11d

	
	rdtsc
	subl	%r12d, %eax
	movl	%eax, %r8d
	movl	%r13d, %ecx
	movl	%r12d, %edx
	movl	%r8d, %esi
	leaq	.LC0(%rip), %rdi
	movl	$0, %eax
	call	printf@PLT
	addl	$1, %r8d
.L2:
	cmpl	$999999, %r8d
	jle	.L3
	movl	$199, %eax
	addq	$8, %rsp
	popq	%rbx
	popq	%r12
	popq	%r13
	popq	%rbp
	.cfi_def_cfa 7, 8
	ret
	.cfi_endproc
.LFE0:
	.size	main, .-main
	.ident	"GCC: (GNU) 8.2.1 20181127"
	.section	.note.GNU-stack,"",@progbits
