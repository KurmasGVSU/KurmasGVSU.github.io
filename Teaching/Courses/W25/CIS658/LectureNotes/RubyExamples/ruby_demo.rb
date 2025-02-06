puts "Hello, World!"

x = 5
y = 6
z = x + y
puts "5 + 6 = #{z}"

if z % 2 == 0
  puts "#{z} is even"
else
  puts "#{z} is odd"
end

def greet(first_name)
  puts 'Hello, #{first_name}. It\'s nice to meet you'
end

greet("George")

name = "bob"
puts "Hello, #{name.capitalize}"

# Symbol: immutable string
# build_distribution(:uniform)
# build_distribution(:normal)

my_dog = {
  name: "Spot",
  age: 4,
  weight: 14,
  breed: :boxer,
}

puts "My dog's name is #{my_dog[:name]}"

my_dog[:age] += 1

sparse_array = {
  1 => 17,
  5 => 22,
  8 => -3,
}

my_other_dog = {
  :name => "Fido",
  :age => 13,
  :weight => 21,
}

students = [
  { first_name: "Ella", last_name: "Smith", gpa: 3.6, credit_hours: 103 },
  { first_name: "Liam", last_name: "Johnson", gpa: 2.9, credit_hours: 60 },
  { first_name: "Noah", last_name: "Williams", gpa: 3.8, credit_hours: 15 },
  { first_name: "Olivia", last_name: "Brown", gpa: 3.2, credit_hours: 75 },
  { first_name: "Emma", last_name: "Jones", gpa: 3.4, credit_hours: 93 },
  { first_name: "Ava", last_name: "Garcia", gpa: 1.7, credit_hours: 50 },
  { first_name: "Sophia", last_name: "Martinez", gpa: 2.8, credit_hours: 80 },
  { first_name: "Isabella", last_name: "Davis", gpa: 3.5, credit_hours: 60 },
  { first_name: "Mia", last_name: "Rodriguez", gpa: 1.0, credit_hours: 40 },
  { first_name: "Amelia", last_name: "Hernandez", gpa: 3.9, credit_hours: 105 },
]

on_probation = students.filter do |s|
  s[:gpa] < 2.0
end

p on_probation

def sum_list(numbers)
  sum = 0
  numbers.each do |num|
    sum += num
  end
  sum # <---- yes, this is common in ruby
end

def my_each(list)
  index = 0
  while index < list.count
    yield list[index]
  end
end

def is_increasing(numbers)
  numbers.each_with_index do |num, index|
    if index > 0 && num < numbers[index - 1]
      return false
    end
  end
  true
end

def is_increasing_v2(numbers)
  numbers.each_with_index do |num, index|
    return false if index > 0 && num < numbers[index - 1]
  end
  true
end

def is_increasing_v3(numbers)
  numbers.each_cons(2) do |a, b|
    return false if a > b
  end
  true
end

def filter(list)
  answer = []
  list.each { |item| answer << item if yield item }
  answer
end

array = [1, 2, 3, 4, 5, 6, 7]
squared = array.map { |item| item * item }
puts squared.inspect
p squared

names = [
  { first: "George", last: "Washington" },
  { first: "John", last: "Adams" },
  { first: "Thomas", last: "Jefferson" },
  { first: "James", last: "Madison" },
  { first: "James", last: "Monroe" },
]

names.map! { |name| "#{name[:last]}, #{name[:first]}" }
p names

class String
  def first
    self[0]
  end
end

puts "Hello".first
