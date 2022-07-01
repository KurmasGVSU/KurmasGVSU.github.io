module MIPSUnit
  module MSpec

    # Represents a label in the +.globl+ section of the code under test.
    # Mistakes when writing specs using such labels can be quite difficult to track down.
    # Therefore, we require users to instantiate this special class (as opposed to an ordinary +String+)
    # so that they must be especially careful and intentional about their use.
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2013
    class AssemblyLabel < String
      def initialize(data)
        super(data)
      end
    end
  end
end