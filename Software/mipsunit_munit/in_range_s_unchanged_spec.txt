describe :in_range do # and verify that the $s registers are unchanged

  before do
    # set each "s" register to a known value
    @s_values = {}
    (0..7).each { |i| @s_values["s#{i}".to_sym] = (10*i) }
    set(@s_values)
  end

  it "returns true if value is in range" do
    call 10, 20, 15
    verify :v0 => true
    verify @s_values
  end

  it "returns false if value is outside range" do
    call 10, 20, 25
    verify :v0 => false
    verify @s_values
  end

  it "returns true if value is equal to minimum" do
    call 10, 20, 10
    verify :v0 => true
    verify @s_values
  end

  it "returns true if value is equal to maximum" do
    call 10, 20, 20
    verify :v0 => true
    verify @s_values
  end
end