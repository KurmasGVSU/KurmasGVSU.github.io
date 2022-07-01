#! /usr/bin/ruby
require 'optparse'

#
# This is the original, "simple" version of this program.  This
# program doesn't actually use this function, but looking at the code
# may help you better understand what the new, more complex program
# does and how it works.
#

def simple_version  # deprecated
  sum = 0
  program = ARGV[0]
  samples = 2500
  param = ARGV[1]
  (1..samples).each do |i|
    output = `#{program} #{param}`	
    output =~ /Time: (\d+)/
    time = $1.to_i
    
    # throw out timings for which the
    # timer "wrapped"
    sum += time if (time < 10000000)
  end
  
  # print the result
  STDERR.puts if (progress)
puts "#{sum / samples}"
end


#
# The "real" program begins here
#



##################################################
#
# look for command-line options
#
##################################################

samples = 2500  # by default, take 2500 samples.  Can be overridden by the --sample option.
progress = true # by default, show progress with dots.
OptionParser.new do |opts|
  opts.banner = "Usage:  average_runs command_to_run [params for underlying program]"
  
  opts.on("-s", "--samples num_samples", "Number of samples to average") { |s| samples = s.to_i}
  opts.on("-p", "--[no-]progress", "Display progress") { |p| progress=p}
end.parse!



########################################################
#
# prepare the command line for the underlying program.
#
########################################################

# remove first item from ARGV and place in program.
program=ARGV.shift

# Join the remaining argmuments into a single string.  Put quotes
# around each argument Placing quotes around the arguments makes it
# clear whether the user means "a" "b" "c", or "a b c" (i.e., three
# separate parameters, or one parameter that contains spaces.
params = ARGV.map {|item| "\"#{item}\""}.join(" ")



########################################################
#
# main loop
#
########################################################

sum = 0
(1..samples).each do |i|
  if (progress && i % 100 == 0)
    STDERR.print "." 
    STDERR.flush
  end
  output = `#{program} #{params}`	
  output =~ /Time: (\d+)/
  time = $1.to_i

  # throw out timings for which the
  # timer "wrapped"
  sum += time if (time < 10000000)
end

# print the result
STDERR.puts if (progress)
puts "#{sum / samples}"
