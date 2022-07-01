module MIPSUnit
  module MSpec

    # Represents a label.  The main purpose of this class is to
    # provide a mechanism for generating label names that are both
    # meaningful and unique in the test file.   A label has two names:
    #
    # * A _local_ name that is suggested by the user and used in the spec code.
    #   This name is stored and reported as a +Symbol+.
    # * A _unique_ name that is guaranteed to be globally unique and is used in the
    #   generated assembly code.  This name is stored and reported as a +String+.

    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012
    #--
    # This class guarantees the uniqueness of global names by simply counting the number
    # of occurrences of each local name and appending two underscores and the count
    # to the local name
    class Label

      # The "local" name of this label -- the name the programmer uses in the spec code
      attr_reader :local_name


      # The "global" name of this label -- a globally unique string placed in the assembly code
      attr_reader :unique_name

      DEFAULT_LABEL_NAME = :mipsunit_label

      def self.reset
        @@label_counts = {}
      end

      private_class_method :reset

      reset

      # Creates a new Label with the given local name and a globally
      # unique name based on the local name.  The parameter must either be a +Symbol+, or
      # have a +to_sym+ method that does not return +nil+.
      def initialize(name=DEFAULT_LABEL_NAME)
        @local_name = name.to_sym
        if @local_name.nil?
          raise ArgumentError.new("Parameter #{name} produces a nil symbol")
        end

        # We use "send" so we don't make increment_count visible to users outside
        # this class.
        @unique_name = "#{@local_name}__#{increment_count(@local_name)}"
      end

      private

      # Increments the count for the given local name
      def increment_count(name)
        raise new ArgumentError unless name.is_a? Symbol
        @@label_counts[name] ||= 0
        @@label_counts[name] += 1
      end
    end
  end
end