def iterate(a)
  max_iters = 4096
  x = 0
  count = 1
  while x < 1000 && count < max_iters
    x = x * x - a
    count += 1
  end

  count == max_iters ? x : nil
end


if ARGV.length == 1
  a = ARGV[0].to_f
  puts "Limit when beginning at #{a}:  #{iterate(a)}"
else
  i = ARGV[0].to_f
  stop = ARGV[1].to_f
  step = ARGV[2].to_f

  while i < stop
    puts "#{i}\t#{iterate(i)}"
    i += step
  end
end