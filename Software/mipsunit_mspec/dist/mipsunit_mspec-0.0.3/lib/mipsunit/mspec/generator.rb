require 'mipsunit/mspec/global'
begin
  require 'mipsunit/mspec/assembly'
rescue LoadError
  $stderr.puts "The auto-generated file 'assembly.rb' is missing."
  $stderr.puts "See README.txt for instructions on generating this file."

  exit MIPSUnit::MSpec::Global::ASSEMBLY_FILE_MISSING
end

require 'mipsunit/mspec/test_factory_helper'
require 'mipsunit/mspec/data_label'

module MIPSUnit
  module MSpec

    # Generates the MIPS assembly code for the provided TestDefinition objects.
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012
    class Generator

      # Generate the MIPS test assembly code for the given TestDefinition (which must be in the given TestDefinitionGroup)
      #--
      # It is necessary to specify the group because the group contains
      # the description of this test as well as any before blocks.  I considered
      # adding the TestDefinitionGroup as an instance variable in TestDefinition;
      # but, decided that having the two-way references would be difficult
      # to test and maintain.
      def self.make_test(definition, group, output)
        # print the test header
        output.puts
        output.puts '#'
        output.puts "# #{group.cumulative_description} #{definition.description}"
        output.puts '#'

        # instantiate the TestFactory
        factory = definition.factory_class.new(output, definition.description)

        # run any before blocks
        group.cumulative_before.each do |block|
          factory.instance_eval(&block)
        end

        # enable the data method (so users can add labels in the 'it' blocks)
        TestFactoryHelper.remove_warning_from_data_method(factory)

        # generate the assembly code for the test
        factory.instance_eval(&definition.block)
      end


      # Generate the MIPS test assembly for all the test definitions in the given TestDefinitionGroup
      # (effectively generates the assembly code, recursively, for all TestDefinitions in the corresponding +define+ block)
      def self.make_group(group, output)
        # print the group header
        elipses = group.nested_groups.size > 0 ? "..." : ""
        output.puts
        output.puts '#############################################'
        output.puts '#'
        output.puts "# #{group.cumulative_description} #{elipses}"
        output.puts '#'
        output.puts "#############################################"

        # generate each test at this level
        group.definitions.each do |definition|
          make_test(definition, group, output)
        end

        # process any nested groups
        group.nested_groups.each do |nested_group|
          make_group(nested_group, output)
        end
      end

      # print the assembly for all data labels.
      def self.print_labels(output)
        output.puts "\n.data"
        output.puts "# Data Labels:"
        DataLabel.all_labels.each do |data_label|
          output.puts data_label.to_s
        end
      end

      # Generate the MIPS test assembly code
      def self.generate(groups, output)
        # print the file header
        output.puts "#"
        output.puts "# This file automatically generated using MIPSUnit::MSpec"
        output.puts "#"
        output.puts
        output.puts ".globl main"
        output.puts
        output.puts ".text"
        output.puts "main:"


        # generate the code for each TestDefinitionGroup (i.e., +describe+ block)
        groups.each do |group|
          make_group(group, output)
        end

        output.puts

        # This prints the "All tests pass." message
        # and exists.  This is *not* a procedure
        output.puts Assembly::ALL_TESTS_PASS


        # include the FAIL procedure
        output.puts Assembly::FAIL

        print_labels(output)
      end
    end # class Generator
  end # module MSpec
end # module MIPSUnit
