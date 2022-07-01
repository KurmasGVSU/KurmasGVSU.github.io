require 'stringio'
require "mipsunit/mspec/generator"
require "mipsunit/mspec/test_factory"
require "mipsunit/mspec/util/util"


module MIPSUnit
  module MSpec
    # runs mspec:  Parses the command line and executes the desired commands
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012
    class Runner

      # Name used in the "usage" output
      NAME = File.basename($PROGRAM_NAME)

      # value returned when the input file is not valid Ruby code
      UNPARSABLE_SPEC_FILE = 1

      # value returned if program is not given enough / correct command-line arguments
      BAD_USAGE = 2
      BAD_FILE = 3

      # value returned when the spec file parses, but running the test generates an exception
      INVALID_SPEC_FILE = 4

      # result of running --version, --help, etc.
      META_OUTPUT = 5

      ASSEMBLY_FILE_MISSING = MIPSUnit::MSpec::Global::ASSEMBLY_FILE_MISSING

      def self.exit_wrapper(value)
        exit(value)
      end

      def self.quit(message, value)
        $stderr.puts(message)
        exit_wrapper(value)
      end

      def self.usage
        quit("Usage: #{NAME} filename", BAD_USAGE)
      end

      def self.run_and_rescue
        begin
          yield
        rescue InvalidSpecException => e
          $stderr.puts "Invalid spec file."
          $stderr.puts e.message
          # prints the backtrace so users can see the offending line of their file
          $stderr.puts
          $stderr.puts e.backtrace
          exit_wrapper INVALID_SPEC_FILE
        end
      end

      def self.process_file(file_name)
        if file_name == '-'
          run_and_rescue { eval($stdin.read) }
        else
          if !File.exist?(file_name) then
            quit("File \"#{file_name}\" does not exist.", BAD_FILE)
          end

          if !File.readable?(file_name) then
            quit("File \"#{file_name}\" is not readable.", BAD_FILE)
          end

          if !File.file?(file_name) then
            quit("File \"#{file_name}\" is not a regular file.", BAD_FILE)
          end

          run_and_rescue { load(file_name) }
        end

        run_and_rescue do
          io = StringIO.new
          Generator.generate(MIPSUnit::MSpec.global.groups, io)
          $stdout.puts io.string
        end

        #begin
        #  # The StringIO object allows us to defer printing anything to stdout
        #  # until we are sure that there aren't any problems with the spec.
        #  # (In other words, we don't want the output stream to print part of a test
        #  # file, only to bail when an exception gets raised.)
        #  io = StringIO.new
        #  Generator.generate(MIPSUnit::MSpec.global.groups, io)
        #  $stdout.puts io.string
        #rescue InvalidSpecException => e
        #  $stderr.puts "Invalid spec file."
        #  $stderr.puts e
        #  exit INVALID_SPEC_FILE
        #end
      end

      private_class_method :exit_wrapper

      def self.run(args)
        if args.include?("--version")
          quit("MIPSUnit::MSpec version #{MIPSUnit::MSpec::VERSION}", META_OUTPUT)
        elsif (args.size < 1)
          usage
        else
          file_name = args[0]
          process_file(file_name)
        end
      end

    end # class Runner
  end # module MSpec
end # module MIPSUnit
