describe :multiply_array do
  it "modifies the array" do
    data(:spec_level_array => [:word, 5, 10, 15, 20])
    call(:spec_level_array, 2, 4)
    verify_memory(:spec_level_array, [:word, 10, 20, 30, 40])
  end

  it "returns the modified array " do
    data(:spec_level_array => [:word, 20, 30, 40, 50])
    call(:spec_level_array, 3, 4)
    verify_memory(:v0, [:word, 60, 90, 120, 150])
  end
end