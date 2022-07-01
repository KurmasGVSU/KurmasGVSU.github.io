describe :in_range do
  it "returns true if value is in range" do
    call 10, 20, 15
    verify :v0 => true
  end

  it "returns false if value is outside range" do
    call 10, 20, 25
    verify :v0 => false
  end

  it "returns true if value is equal to minimum" do
    call 10, 20, 10
    verify :v0 => true
  end

  it "returns true if value is equal to maximum" do
    call 10, 20, 20
    verify :v0 => true
  end

  it "does not modify either s0 or s1" do
    set(:s0 => 1343, :s1 => 1557)
    call 10, 20, 15
    verify(:s0 => 1343, :s1 => 1557)
  end
end