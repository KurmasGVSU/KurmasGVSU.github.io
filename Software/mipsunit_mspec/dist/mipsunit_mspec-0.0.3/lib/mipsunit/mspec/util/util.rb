module MIPSUnit
  module MSpec
    module Util
      class Util
        # return a Hash containing a random value to each of the preserved registers, except $ra
        # Author:: Zachary Kurmas
        # Copyright:: Copyright (c) 2013
        def self.randomize_preserved
          s_regs = (0..7).map { |i| "s#{i}".to_sym }
          (s_regs + [:gp, :sp, :fp]).inject({}) { |h, reg| h[reg] = rand(16383); h }
        end
      end
    end
  end
end