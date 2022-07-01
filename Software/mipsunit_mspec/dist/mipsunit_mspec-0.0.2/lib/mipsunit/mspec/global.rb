module MIPSUnit
  module MSpec

    # A container for MSpec global data
    #
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012
    class Global

      ASSEMBLY_FILE_MISSING = 5

      def initialize
        @test_groups = []
        @data_in_before = nil
      end


      # Append +group+ to the list of test groups.  
      # (The +define+ method in the global namespace
      # uses this method to store the test groups it creates.)
      def add_group(group)
        @test_groups << group
      end


      # get test groups
      def groups
        @test_groups
      end

      # print a warning just one time per process
      def print_single_warning(warning)
        if (warning == :data_in_before)
          unless @data_in_before
            message = "WARNING!  Calling #data in a 'before' block will generate a separate label for each test. "
            message += "If the code under test should not modify the data in memory,"
            message += " call .data outside the 'before' block. "
            message += "If the code under test should modify the data, then use #data! to suppress this warning."
            $stderr.puts(message)
            @data_in_before = true
          end
        else
          raise IllegalArgumentException.new("Parameter #{warning} unrecognized")
        end
      end

    end # end class Global    
  end # end MIPSUnit
end # end MSpec
