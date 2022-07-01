require 'mipsunit/mspec/test_definition'
require 'mipsunit/mspec/test_definition_group'
require 'mipsunit/mspec/test_factory_helper'
require 'mipsunit/mspec/data_label'

module MIPSUnit
  module MSpec

    # Objects of type TestFactory provide the context in which
    # test definitions are run.  First, it provides the instance
    # methods like #set, #call, and, #verify that generate the
    # assembly code.
    #
    # Second, it provides the mechanism that allows +describe+ blocks
    # to provide a scope for helper methods and instance variables.
    # Each +describe+ block corresponds to a subclass of TestFactory.
    # Any instance methods/variables defined within a +describe+ block
    # become instance methods/variables in the corresponding
    # TestFactory subclass.
    #
    # A +describe+ block contains +it+ blocks.  Each +it+ block
    # defines a test (i.e., generates a TestDefinition object).  All
    # of the TestDefinition objects in a +describe+ block are stored
    # in a TestDefinitionGroup object.  The +Class+ objects describing
    # TestFactory and its subclasses contain a <code>@metadata</code> instance
    # variable that contains this TestDefinitionGroup object.
    #
    # TestFactory instances have three instance variables: <code>@description</code>, <code>@output</code>,
    # and <code>@local_labels</code>. Use of these instance variables inside +before+ or +it+ blocks can have
    # unintended consequences.
    #
    # Similarly, TestFactory class objects (created by +describe+ blocks) have a <code>@metadata</code> instance
    # variable. Use of <code>@metadata</code> inside a +describe+ block can have unintended consequences.
    #
    # Author:: Zachary Kurmas
    # Copyright:: (c) 2012
    class TestFactory
      AL = AssemblyLabel

      # return the metadata for the particular TestFactory subclass
      def self.metadata
        @metadata
      end

      # Processes a +describe+ block in a spec
      def self.describe(description, &block)
        # save metadata in a local variable so we can pass it to the
        # new TestDefinitionGroup while in a block
        my_metadata = @metadata

        # Define a new descendant of TestFactory (this is what Class.new(self) does)
        # Every class is described by an instance of Class.  The block below
        # adds the instance variable @metadata to the newly defined Class object.
        group = Class.new(self) do
          @metadata = TestDefinitionGroup.new(description, my_metadata)
          # A define block looks like this:
          # define "some function" do
          #   THE BLOCK
          # end
          #
          # The class_eval statement below runs the code in the define
          # block (i.e, "THE BLOCK") in the context of defining a new class.  In other
          # words, it is as if we had written the code like this:
          #
          # class NewTestFactory < TestFactory
          #  THE BLOCK
          # end
          #
          # Thus, any definitions in the block become instance methods for this
          # descendant of TestFactory.  Calls to "it" or "before" are calls to the
          # class methods "it" and "before" defined below.
          class_eval(&block)
        end

        # Add the metadata for the newly processed describe block (i.e., group.metadata) to the
        # current list of nested groups.  Notice that @metadata in the  line below is not the same
        # object as @metadata in the block above.  @metadata in the block above is an instance variable in the
        # newly created Class object.
        @metadata.nested_groups << group.metadata unless @metadata.nil?
        group.metadata
      end

      # Generates a TestDefinition object then adds it to the list of
      # test definitions for the current TestDefinitionGroup
      def self.it(description, &block)
        definition = TestDefinition.new(self, description, block)
        @metadata.definitions << definition
        description
      end

      # provide code to be run before each test.
      def self.before(&block)
        @metadata.before << block
      end


      # Define a new data label whose scope is the entire +describe+ block
      def self.data(hash)
        TestFactoryHelper.data(hash) { |dl| @metadata.add_data_label(dl) }
      end

      # define a new data label whose scope is limited to the current +it+ block.
      # This method should not be called from a +before+ block, hence the warning.
      # Once the +before+ blocks have run, this implementation is replaced with another
      # that does not include the warnings.
      def data(hash)
        MIPSUnit::MSpec.global.print_single_warning(:data_in_before)
        data!(hash)
      end

      # define a new data label whose scope is limited to the current +it+ block.  Calling this method from a
      # +before+ bock will create a separate label for each +it+ block, which is wasteful if done needlessly; hence
      # the "!". (The method #data from within a +before+ block prints a warning.  Calling this method suppresses
      # that warning.)
      def data!(hash)
        TestFactoryHelper.data(hash) do |dl|
          self.class.metadata.assert_label_not_defined_in_object_scope(dl)
          @local_labels << dl
        end
      end

      def initialize(output, description)
        @output = output
        @description = description
        @local_labels = []
      end

      # Generates assembly instructions to set registers to the given values.
      # Values may be integers (+Integer+) or boolean (+true+) or (+false+). This
      # method will raise an InvalidSpecException if a hash key is not a valid register
      # or if a value is not a valid data type.
      #
      def set(hash)
        labels_in_scope = self.class.metadata.data_labels + @local_labels
        # we iterate the hash in sorted order so that the output is predictable for
        # testing purposes
        hash.keys.sort { |a, b| a.to_s <=> b.to_s }.each do |key|
          TestFactoryHelper.set_register(key, hash[key], @output, labels_in_scope)
        end
      end

      # Generates assembly instructions to call the specified function with the given values.
      def call_by_name(name, p1=nil, p2=nil, p3=nil, p4=nil)
        if name.nil?
          raise InvalidSpecException.new("Must specify a method when using call_by_name.")
        end
        name = name.to_s

        # remove unused parameters
        params = {:a0 => p1, :a1 => p2, :a2 => p3, :a3 => p4}.reject { |k, v| v.nil? }
        set(params)

        @output.puts "jal #{name}"
      end

      # Generates assembly instructions to call the method identified by the describe block with the given values.
      def call(p1=nil, p2=nil, p3=nil, p4=nil)
        name = self.class.metadata.subject
        call_by_name(name, p1, p2, p3, p4)
      end

      # The generated assembly code uses :at as a temporary.  Thus, it is not possible to
      # directly verify the value of :at
      def assert_register_not_at(register)
        if (register == :at || register == :r1)
          name = TestFactoryHelper.register_to_string(register)
          message = "Cannot verify value of #{name} because MSpec uses it as a temporary."
          message += "\nTo verify the value of this register, you must first move it to a different, unused register "
          message += "and verify the value of that register."
          raise InvalidSpecException.new(message)
        end
      end


      # Generates assembly code to verify that the specified register has the expected value.
      def verify_register(register, expected_value)
        assert_register_not_at(register)

        unless expected_value.is_a?(Integer) || expected_value.is_a?(Symbol) ||
            expected_value.is_a?(TrueClass) || expected_value.is_a?(FalseClass)
          raise InvalidSpecException.new("Cannot compare a register (#{register.to_s}) to type #{expected_value.class}")
        end

        TestFactoryHelper.validate_register(register)

        # Generate a single string containing the complete name of this test
        test_name_string = "#{self.class.metadata.cumulative_description} #{@description}"

        # put desired value in $at  (Since the mips beq instruction only compares registers, this happens anyway.
        # There is no harm in making it explicit here.)
        set(:at => expected_value)

        register_name = TestFactoryHelper.register_to_string(register)
        TestFactoryHelper.fail_unless("beq $at, #{register_name}", test_name_string, "Register #{register.to_s}",
                                      expected_value, self, @output) do
          @output.puts "move $a3, #{register_name}"
        end
      end

      # verifies the given output state
      # TODO: call verify_memory when the meaning is unambiguous
      def verify(hash)
        # We sort the keys before iterating only so that the
        # output is consistent for testing purposes.
        hash.keys.sort { |a, b| a.to_s <=> b.to_s }.each do |key|
          verify_register(key, hash[key])
        end
      end

      # verify that the given memory location contains the specified value
      #-- verify_memory(:t8, [:word, 1, 2, 3, 4]) # make sure words at t8 are 1, 2, 3, and 4
      # verify_memory(:some_label, [:word, 1, 2, 3, 4]) # make sure words at :some_label are 1, 2, 3, and 4
      # verify_memory("some_label", [:word, 1, 2, 3, 4]) # make sure words at "some_label" are 1, 2, 3, and 4
      # verify_memory(:t8, :t7, 8) make sure the 8 bytes at memory addresses t8 and t7 are identical
      # verify_memory(:some_label, :some_other_label, 8) make sure the 8 bytes at memory labeled memory addresses are
      # identical
      def verify_memory(observed, expected, length = nil)
        assert_register_not_at(observed) if observed.is_a?(Symbol)

        raise InvalidSpecException.new("Feature not yet supported") unless (observed.is_a?(String) || observed.is_a?(Symbol))
        raise InvalidSpecException.new("Feature not yet supported") unless expected.is_a?(Array)

        if (expected.is_a?(Array))
          labels_in_scope = self.class.metadata.data_labels + @local_labels
          test_name_string = "#{self.class.metadata.cumulative_description} #{@description}"
          TestFactoryHelper.verify_memory_array(observed, expected, self, test_name_string, labels_in_scope, @output)
        end
      end
    end # end class
  end # end module
end #module
