module MIPSUnit
  module MSpec

    # TestDefinition objects Bundle
    # * Code for a test (i.e., its "definition"),
    # * the description of the test, and
    # * the specific factory needed to build the test.
    #
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012
    class TestDefinition

      # the specific TestFactory subclass used to generate
      # the assembly code for this test
      attr_reader :factory_class

      # the textual description of this test (i.e., the +String+ passed to the +it+ block)
      attr_reader :description

      # the test definition itself (i.e., the block passed to +it+)
      attr_reader :block

      def initialize(test_factory_class, description, block)
        @factory_class, @description, @block = test_factory_class, description, block
      end
    end # class
  end # MSPec
end # MIPSUnit
