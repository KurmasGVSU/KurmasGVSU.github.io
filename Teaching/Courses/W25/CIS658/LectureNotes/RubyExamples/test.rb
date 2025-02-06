["elegant", "awesome", "unique"].each do |desc|
  puts "Ruby is #{desc}!"
end

# Sum of even squares <= 10
ans = (1...10).select { |i| i % 2 == 0 }.map { |j| j * j }.inject(0) { |sum, k| sum + k }
print(ans)
