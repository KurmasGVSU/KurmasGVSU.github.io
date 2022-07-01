require 'mipsunit/mspec/label'

module MIPSUnit
  module MSpec

    # Represents a label in an assembly file's +.data+ section.  This class
    # - generates a Label object with the given local name
    # - generates the corresponding line of assembly code for the +.data+ section
    # Author:: Zachary Kurmas
    # Copyright:: Copyright (c) 2012
    class DataLabel

      @@labels = []

      # A +Hash+ whose keys are the allowed data directives (+:word+, +:byte+, +:asciiz+, etc.)
      DATA_DIRECTIVES = {:align =>1, :byte => 1, :half => 2, :word => 4,
                         :ascii => 1, :asciiz => 1,
                         :double => 8, :float => 4}

      # the underlying Label object
      attr_reader :label

      # the line of assembly code to initialize the label.
      attr_reader :data_string


      # returns +true+ if +directive+ specifies a data size (+:word+, +:byte+, etc.)
      def self.is_size_directive?(directive)
        return false if directive == :align
        return false if directive == :space
        DATA_DIRECTIVES.has_key?(directive)
      end

      # returns the size (in bytes) corresponding to the given directive
      #
      # raises InvalidSpecException if the directive does not correspond to a size (e.g., +:space+, +:align+)
      def self.size_of (directive)
        raise InvalidSpecException.new(".align does not have a size and cannot be used in this context") if directive ==:align
        raise InvalidSpecException.new(".space does not have a size and cannot be used in this context") if directive == :space
        raise InvalidSpecException.new("#{directive.to_s} is not a valid data type") unless is_size_directive?(directive)
        DATA_DIRECTIVES[directive]
      end

      # converts the given datum for a data label to the corresponding +String+.
      # * Integer and Float are unchanged
      # * String are placed in quotation marks
      # * +true+ becomes 1 and +false+ becomes 0
      # * Symbols are converted into the corresponding directive
      #   (+:word+ is converted to +.word+, +:ascii+ to +.ascii+, etc.)
      # +name+ is used for error reporting only
      #
      # raises an InvalidSpecException if the datum is not recognized.
      def self.data_item_to_string(datum, name=nil)
        if (name.nil?)
          name = ""
        else
          name = "#{name}: "
        end
        if datum.is_a?(Symbol) && DATA_DIRECTIVES.has_key?(datum)
          ".#{datum.to_s}"
        elsif datum.is_a?(Integer) || datum.is_a?(Float)
          datum.to_s
        elsif datum.is_a?(String)
          %!"#{datum}"!
        elsif datum.is_a?(TrueClass)
          "1"
        elsif datum.is_a?(FalseClass)
          "0"
        elsif datum.nil?
          raise InvalidSpecException.new("#{name}Data items may not be nil.")
        else
          raise InvalidSpecException.new(%!#{name}"#{datum.inspect}" is not a valid data item.!)
        end
      end


      # Convert the array of values into a +String+ to be placed in
      # an assembly file's +.data+ section.  The +name+ parameter is used
      # only to generate error messages.
      def self.make_data_string(name, *data)
        data.map { |item| data_item_to_string(item, name) }.join(' ')
      end

      # returns a list of all DataLabel objects that have been created
      def self.all_labels
        @@labels
      end

      # generate a new DataLabel with the given local name and array of values/directives
      def initialize(local_name, *data)
        @label = Label.new(local_name)
        @data_string = DataLabel.make_data_string(local_name, *data)
        @@labels << self
      end

      # return the local name of this label
      def local_name
        @label.local_name
      end

      # return the unique name of this label
      def unique_name
        @label.unique_name
      end

      # return the line of assembly text for this label
      def to_s
        "#{@label.unique_name}: #{@data_string}"
      end
    end
  end
end
