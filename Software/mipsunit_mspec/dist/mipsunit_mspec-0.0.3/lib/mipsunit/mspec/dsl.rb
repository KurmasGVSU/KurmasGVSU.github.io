require 'mipsunit/mspec/test_factory'


module MIPSUnit
  module MSpec
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012

    #--
    # This code is placed in a file by itself so that we can avoid
    # including it when running RSpec tests on the code.  (If a spec file includes
    # this code, then the describe method below "hides" the RSpec describe method.)

    #
    # This module adds the describe method to the top-level namespace.
    # The most straightforward way to do this would be to define at  the top level; however,
    # doing so would add a describe method to *every* object in the system.  By putting describe
    # in its own DSL module then extending/including it, we limit where the method is added.
    #
    # The code in this file mimics Myron Martson's design for RSPec.
    # See http://stackoverflow.com/questions/12826270/why-use-extend-include-instead-of-simply-defining-method-in-main-object
    # for a more thorough explanation.
    module DSL
      def describe(name, &block)
        MIPSUnit::MSpec.global.add_group(MIPSUnit::MSpec::TestFactory.describe(name, &block))
      end
    end
  end
end

# adds the describe method above to the main object.
extend MIPSUnit::MSpec::DSL

# Adds the describe method above to all modules
Module.send(:include, MIPSUnit::MSpec::DSL)

