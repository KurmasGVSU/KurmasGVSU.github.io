require 'mipsunit/mspec/invalid_spec_exception'

module MIPSUnit
  module MSpec

    # A (potentially nested) group of TestDefinition objects and related meta-data
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012

    class TestDefinitionGroup

      # the description for this group (i.e., the parameter passed to the +describe+ block)
      attr_reader :description

      # the list of TestDefinition objects immediately contained in the +describe+ block
      attr_reader :definitions

      # the list of +before+ blocks
      attr_reader :before

      # the list of nested TestDefinitionGroup objects (These tend to correspond to nested +describe+ blocks.)
      attr_reader :nested_groups

      # the name of the function under test.  (May be +nil+)
      attr_reader :subject

      def initialize(description, parent)
        @description = description.to_s
        @definitions = []
        @before = []
        @nested_groups = []
        @data_labels = []
        @parent = parent

        # This assumes that this object being initialized has not yet
        # been added to the parent's list of nested groups
        #  @order = parent.nil? ? 0 : @parent.nested_groups.size

        # if the description is a symbol, then assume that symbol
        # corresponds to the name of the procedure to test (i.e., the
        # "subject").  If the description is not a symbol, see if some
        # outer describe block did define a subject.  If there is no
        # parent, then there is no specified subject.  (Not specifying
        # a subject just means that calls to "it" must contain an
        # explicit parameter.)  Throw an exception if both this
        # describe block and some parent block attempt to specify a subject.
        if (description.is_a? Symbol)
          if @parent.nil? || @parent.subject.nil?
            @subject = description.to_s
          else
            raise InvalidSpecException.new("Error: Nested descriptions may specify only one subject." +
                                               "(In other words, you can't pass a symbol to a nested description " +
                                               "if some parent description is also described using a symbol.)")
          end # inner else
        else
          @subject = @parent.nil? ? nil : @parent.subject
        end # outer else
      end


      # Concatenate this group's description and the descriptions of all of its ancestors
      def cumulative_description
        parent_description = @parent.nil? ? "" : "#{@parent.cumulative_description} "
        "#{parent_description}#{description}"
      end


      # Get all the before blocks for this group and its ancestors
      def cumulative_before
        @parent.nil? ? before : @parent.cumulative_before + before
      end


      # return an immutable array containing this object's data labels
      def local_data_labels
        @data_labels.dup.freeze
      end

      # return an immutable array containing the labels defined for all ancestors
      def ancestor_labels
        (@parent.nil? ? [] : (@parent.ancestor_labels + @parent.local_data_labels)).freeze
      end

      # return an immutable array containing the labels defined for this object
      # and all descendants
      def descendant_labels
        @nested_groups.map { |ng| (ng.local_data_labels + ng.descendant_labels) }.flatten.freeze
      end

      # returns an immutable array of all data labels "in scope"
      #--
      # We return an immutable shallow copy so that users
      # quickly realize that they should use add_data_label instead of doing
      # something like
      # tf.data_labels << DataLabel.new
      def data_labels
        #(ancestor_labels + local_data_labels + descendant_labels).freeze
        (ancestor_labels + local_data_labels).freeze
      end

      # Add a new data label, and complain if that label's name is not unique
      def add_data_label(label)
        assert_label_not_defined_in_group_scope(label)
        @data_labels << label
      end

      # Raises InvalidSpecException if a label with the same name has already
      # been defined in the scope of this TestDefinitionGroup. A group's scope includes
      # all of its ancestors or descendants.
      def assert_label_not_defined_in_group_scope(label)
        if (data_labels + descendant_labels).map { |item| item.local_name }.include?(label.local_name)
          raise InvalidSpecException.new "Label \"#{label.local_name}\" already defined in this scope."
        end
      end

      # Raises InvalidSpecException if a label with the same name has already
      # been defined in the scope of a specific TestFactory object. A TestFactory's scope
      # includes the TestDefinitionGroup to which it belongs and all of that group's ancestors.
      def assert_label_not_defined_in_object_scope(label)
        if (data_labels).map { |item| item.local_name }.include?(label.local_name)
          raise InvalidSpecException.new "Label \"#{label.local_name}\" already defined in this scope."
        end
      end
    end # class TestDefinitionGroup
  end # module
end # MIPSUnit
