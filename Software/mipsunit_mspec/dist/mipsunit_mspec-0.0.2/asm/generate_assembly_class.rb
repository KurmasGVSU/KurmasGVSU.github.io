require "#{File.dirname(__FILE__)}/assembly_builder"

target = nil
if ARGV.size == 0
  $stderr.puts "Usage:  assembly_builder target"
  exit
elsif ARGV[0] == "-"
  target = $stdout
elsif ARGV[0] == "default"
  filename = File.expand_path("#{AssemblyBuilder::CURDIR}/../#{AssemblyBuilder::DEFAULT_TARGET}")
  target = File.open(filename, "w")
else
  target = File.open(ARGV[0], "w")
end
AssemblyBuilder.build(target)

