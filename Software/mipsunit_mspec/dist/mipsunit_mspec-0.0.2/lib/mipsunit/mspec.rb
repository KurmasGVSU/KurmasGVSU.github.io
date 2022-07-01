require 'mipsunit/mspec/global'

module MIPSUnit
  # MSpec is an RSpec-inspired unit testing framework for MIPS
  # assembly.  MSpec takes RSpec-like spec files as input and
  # generates an assembly file containing unit tests.
  #
  # Author:: Zachary Kurmas
  # Copyright:: Copyright (c) 2012
  module MSpec

    VERSION = "0.0.2"

    def self.reset
      @global = nil
    end

    # return the singleton Global object.  Create a new Global object if necessary.
    def self.global
      @global ||= Global.new
    end
  end
end

