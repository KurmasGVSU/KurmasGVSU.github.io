require 'mipsunit/mspec/assembly_label'
require 'set'

module MIPSUnit
  module MSpec
    # These helper methods are used by TestFactory#set,
    # TestFactory#call, TestFactory#verify, etc. to generate
    # assembly code. We moved them to this separate class so they don't
    # appear in the namespace of +describe+ blocks.  (It is
    # important to limit what methods appear in the +describe+ block
    # namespace so users don't accidentally write helper methods that
    # hide MSpec methods.)
    #
    # Author:: Zachary Kurmas
    # Copyright:: (c) 2012


    class TestFactoryHelper

      # A +Set+ of valid register names
      REGISTERS = Set.new [:zero, :at, :v0, :v1, :a0, :a1, :a2, :a3]
      REGISTERS.merge((0..7).map { |i| "t#{i}".to_sym })
      REGISTERS.merge((0..7).map { |i| "s#{i}".to_sym })
      REGISTERS.merge([:t8, :t9, :k0, :k1, :gp, :sp, :fp, :ra])
      REGISTERS.merge((0..31).map { |i| "r#{i}".to_sym })
      @@labels = {}
      @@label_num = 0

      # return whether +symbol+ is a valid register
      def self.is_register?(symbol)
        REGISTERS.include?(symbol)
      end

      # raises an InvalidSpecException if +register+ is not a valid register
      def self.validate_register(register)
        if !REGISTERS.include?(register)
          raise InvalidSpecException.new("#{register} is not a valid register.")
        end
      end

      def self.register_to_string(register)
        validate_register(register)
        reg_string = register.to_s
        if reg_string =~ /^r(\d+)$/
          "$#{$1}"
        else
          "$#{reg_string}"
        end
      end

      # replaces the initial implementation of TestFactory#data with one that
      # does not print a warning.
      def self.remove_warning_from_data_method(factory)
        def factory.data(hash)
          data!(hash)
        end
      end

      # creates a new DataLabel.  The +yield+ adds the new label to either
      # the specific TestFactory, or the entire block as appropriate.
      def self.data(hash)
        unless hash.is_a?(Hash)
          raise InvalidSpecException.new("Parameters to method \"data\" are incorrectly formatted.")
        end

        hash.each do |name, values|
          unless (name.is_a? Symbol) || (name.is_a? String)
            raise InvalidSpecException.new("Must use symbol or string when specifying data.")
          end
          if TestFactoryHelper.is_register?(name)
            raise InvalidSpecException.new("Data labels may not be register names.")
          end
          dl = DataLabel.new(name, *values)
          yield dl
        end
      end


      # return the unique name for the given label (i.e., local name).
      # Raises an InvalidSpecException if +label+ is not the local name of a label
      # currently in scope.  Raises an ArgumentError if (1) +label+ is not a symbol,
      # or (2) there are multiple labels with the same name currently in scope.
      def self.lookup_unique_name(label, labels_in_scope)
        raise ArgumentError.new("label must be a symbol") unless label.is_a?(Symbol)

        matching_label = labels_in_scope.select { |dl| dl.local_name == label }
        if (matching_label.size == 0)
          raise InvalidSpecException.new("There is no label \"#{label.to_s}\" in this scope.")
        elsif (matching_label.size == 1)
          matching_label.first.unique_name.to_s
        else
          raise ArgumentError.new("TestFactoryHelper.set_address was passed a data label list" +
                                      "that contained duplicate labels. ")
        end
      end


      # return the globally unique +String+ that corresponds to the given label.
      # If +label+ is a +String+, then assume that the name is already unique
      # (e.g., that it is a label hard-coded in the code under test).  If +label+ is a
      # +Symbol+, then assume that it is the local name of a DataLabel and look up its
      # unique name.
      def self.get_label_string(label, labels_in_scope)
        return label if label.is_a?(AssemblyLabel)
        return lookup_unique_name(label, labels_in_scope) if label.is_a?(Symbol)
        raise InvalidSpecException.new("#{label.to_s} is not a valid label.  (Must be  must be a Symbol or AssemblyLabel.)")
      end


      # produce a line of assembly to set +register+ to +value+.  This method
      # will raise an InvalidSpecException if +register+ is not a valid register, or if
      # +value+ is not an integer.  The method will also produce a warning on $stderr if the 
      # given value is outside the range of a 32-bit integer.
      #--
      # TODO: Improve the error messages/warnings..  
      # For example, (1) try to tell the user
      # which line of the spec file, or the name of the test, that is producing the error.
      #  (I'm not sure if this is feasible.)
      #  Or, for invalid values, list the register that we are attempting to set.
      def self.set_integer(register, value, output)
        validate_register(register)

        if !value.is_a? Integer
          raise InvalidSpecException.new("#{value} is not an integer.")
        end

        if value > 0xFFFFFFFF || value < -0x80000000
          $stderr.puts "Warning!  Value #{value} is outside the 32-bit range for integers."
        end

        output.puts "li #{register_to_string(register)}, #{value}"
      end

      # produce a line of assembly to set +register+ to +value+.  This method
      # will raise an InvalidSpecException if +register+ is not a valid register, or if
      # +value+ is not +true+ or +false+. 
      def self.set_boolean(register, value, output)
        validate_register(register)

        if value.is_a?(TrueClass) || value.is_a?(FalseClass)
          set_integer(register, value ? 1 : 0, output)
        else
          raise InvalidSpecException.new("#{value} is not a valid boolean variable.")
        end
      end

      # produce a line of assembly to set +register+ to the unique label corresponding to +label+
      # This method will raise an InvalidSpecException if +register+ is not a valid register, or if
      # +label+ is not a valid label.
      def self.set_address(register, label, output, data_labels)
        validate_register(register)
        output.puts "la #{register_to_string(register)}, #{get_label_string(label, data_labels)}"
      end

      # Write an assembly statement to +output+ setting +register+ to +value+.
      # This method raises an InvalidSpecException if either
      # * register does not refer to a valid register, or
      # * value is not of a recognized type.
      # This method does not accept String values.
      def self.set_register(register, value, output, data_labels)
        if (value.is_a?(TrueClass) || value.is_a?(FalseClass))
          set_boolean(register, value, output)
        elsif value.is_a?(Integer)
          set_integer(register, value, output)
        elsif value.is_a?(Symbol) || value.is_a?(AssemblyLabel)
          set_address(register, value, output, data_labels)
        else
          raise InvalidSpecException.new("Cannot set a register to type #{value.class}.")
        end
      end

      # TODO: Implement set_registers
      # Set the +register+ and those that follow numerically to the values in +value_array+
      # For example, set_registers(:v0, [10, 20], output) would set :v0 to 10 and set :v1 to 20.
      #def self.set_registers(register, value_array, output)
      #end


      # generate assembly code that will test a specified condition, then print a failure message unless the
      # specified condition is met.
      # - +condition+ is a +beq+ or +bne+ instruction without the branch target. (In other words,
      # +condition+ does _not_ include the label.)
      # - +test_name+ is the +String+ that is printed in the failure message to identify the test
      # - +location_name+ is a +String+ containing the name of the location as it should be printed in the error
      # #message (.e.g, "$v0")
      #- +expected_value+ is the expected value that is printed in the error message (must be an integer)
      # - +factory+ the factory
      # - +output+ the output stream
      # The block is responsible for moving the observed value into $a3
      def self.fail_unless(condition, test_name, location_name, expected_value, factory, output)
        # create a label for the branch target (i.e., the end if the "if" block)
        target_label = Label.new("pass")

        # equivalent of (if $at != $register_to_check), then ...
        output.puts "#{condition}, #{target_label.unique_name}"

        # create a data label containing the test name. (This label is used to print an error message if necessary)
        test_name_label = Label.new("test_name")
        factory.class.data(test_name_label.unique_name.to_sym => [:asciiz, test_name])

        # create a data label containing the name of the location to check (e.g., register name, memory location)
        # TODO: Search for existing labels with same data.
        location_name_label = Label.new("reg_name")
        factory.class.data(location_name_label.unique_name.to_sym => [:asciiz, location_name.to_s])

        yield
        factory.call_by_name(:fail, test_name_label.unique_name.to_sym, location_name_label.unique_name.to_sym,
                             expected_value)

        # The label at the end of the if block
        output.puts "#{target_label.unique_name}:"
      end


      # returns +value+ if +value+ is an +Integer+.  Returns the corresponding +ASCII+ value if +value+ is a +String+
      # of length 1,
      def self.get_integer_value(value)
        if (value.is_a?(Integer))
          return value
        elsif (value.is_a?(String))
          if (value.length == 0 || value.length > 1)
            raise ArgumentError.new("If value is a String, it must have length exactly 1.")
          end
          return value.bytes.first
        else
          raise ArgumentError.new("value must be a Integer or a String")
        end
      end

      # generates an assembly +lw+ (load word) instruction.
      # This method generates the appropriate +lw+ pseudo-instruction
      # based on the type of +base+.  If +base+ is a +String+ (which is assumed to be a label) then
      # an instruction of the form "lw target, base+offset" is generated.  If
      # +base+ is a +Symbol+ (which is assumed to be a register), an instruction of the form +lw target, offset(base)+
      # is generated.
      # - _Warning:_ In each case, the resulting machine instructions are likely to use $at.
      # - _Note:_ <code>Symbol</code>s are always assumed to be registers.  This method does not look up local label
      #   names. When using local labels, you must call lookup_unique_name before calling print_lw.
      def self.print_lw(output, target, base, offset, size)
        op= {4 => "lw", 2 => "lh", 1 => "lb"}
        raise ArgumentError.new("Size must be #{op.keys.inspect}") unless op.has_key?(size)
        validate_register(target)
        if (base.is_a?(String))
          output.puts("#{op[size]} $#{target.to_s}, #{base}+#{offset}")
        elsif (base.is_a?(Symbol))
          validate_register(base)
          output.puts("#{op[size]} $#{target.to_s}, #{offset}(#{register_to_string(base)})")
        else
          raise ArgumentError.new("base must be a String or a Symbol")
        end
      end

      # This is a helper method for verify_memory_array that processes a single element of an array containing
      # expected values.
      # - If +value+ is a +String+ or +Integer+, verify that the memory location under test (+base+ +
      #   +current_offset+) is equal to +value+.
      # - If +value+ is a assembler directive (e.g., "+:word", ":byte", ":ascii"), return the corresponding data size.
      #
      # +current_increment+ is the size of the datum under test (i.e., the amount by which the offset should be
      # increased to reach the next value)
      #
      # This method returns two values:
      # - the offset of the next element to be examined, and
      # - the current increment (which changes if +value+ corresponds to an assembler directive)
      def self.process_expected_memory_value(value, base, current_offset, current_increment, factory, test_name,
          output)
        if value.is_a?(Symbol)
          #TODO: properly align data (e.g., :word should move to next 4-byte boundary)
          return [current_offset, DataLabel.size_of(value)]
        else
          current_increment = 1 if (value.is_a?(String))
          # TODO:  When the input is a character, the failure message will print the
          # ASCII value, not the character.  This could lead to confusing error messages.
          int_value = get_integer_value(value)

          if (int_value > -2**15 && int_value <= 2**15)
            print_lw(output, :at, base, current_offset, current_increment)
            output.puts "addi $at, $at, #{-int_value}"

            # TODO Have the failure message print the local name, not the global name.
            fail_unless("beq $at, $zero", test_name, "Memory location\\n#{base}[#{current_offset}]", int_value,
                        factory, output) do
              print_lw(output, :a3, base, current_offset, current_increment)
            end
          else # end if value is 16 bits or less
            print_lw(output, :at, base, current_offset, current_increment)
            output.puts "xori $at, $at, #{int_value & 0x0000ffff}"
            output.puts "sll $at, $at, 16"

            # TODO Have the failure message print the local name, not the global name.
            fail_unless("beq $at, $zero", test_name, "Memory location\\n#{base}[#{current_offset}]", int_value,
                        factory, output) do
              print_lw(output, :a3, base, current_offset, current_increment)
            end
            print_lw(output, :at, base, current_offset, current_increment)
            output.puts "srl $at, $at, 16"
            output.puts "xori $at, $at, #{(int_value >> 16)}"


            # TODO Have the failure message print the local name, not the global name.
            fail_unless("beq $at, $zero", test_name, "Memory location\\n#{base}[#{current_offset}]", int_value,
                        factory, output) do
              print_lw(output, :a3, base, current_offset, current_increment)
            end
          end # if value is 16 bits or more
          [current_offset + current_increment, current_increment]
        end # else
      end


      # Verify that the memory beginning at the given label/register matches the sequence of values given
      def self.verify_memory_array(base, values, factory, test_name, labels_in_scope, output)
        if (is_register?(base))
          label_to_use = base
        else
          label_to_use = get_label_string(base, labels_in_scope)
        end

        current_offset = 0
        current_increment = DataLabel.size_of(:word)

        # TODO: Refactor this out into a separate method
        # (And update the tests as well)
        # replace Strings with the sequence of individual letters
        previous_string_type = nil
        values = values.map do |item|
          previous_string_type = item if (item == :ascii || item == :asciiz)
          if item.is_a?(String)
            if previous_string_type.nil?
              raise InvalidSpecException.new("String values must be tagged as .ascii or .asciiz.")
            elsif previous_string_type == :asciiz
              item.chars.to_a + [0]
            else
              item.chars.to_a
            end
          else
            item
          end
        end
        values.flatten!

        values.each do |value|
          (current_offset, current_increment) = process_expected_memory_value(value, label_to_use, current_offset,
                                                                              current_increment, factory,
                                                                              test_name, output)
        end
      end

      # Verify that the memory beginning at expected_base matches the memory at observed_base
      def self.verify_memory_memory(expected_base, observed_base, factory, labels_in_scope, output)
        # problem:  Need two registers (one for each value loaded)  :at can be one.  No good choice for the second.
      end
    end # end class
  end # MSpec
end # MIPSUnit