describe :sum_array do
  data(:array1 => [:word, 2, 4, 6, 8, 10])

  it "adds all the values in the array" do
    call(:array1, 5)
    verify(:v0 => 30)
  end

  it "adds only the specified number of elements" do
    call(:array1, 4)
    verify(:v0 => 20)
  end

  it "handles negative numbers also" do
    data(:array2 => [:word, 1, -1, 2, -3])
    call(:array2, 4)
    verify(:v0 => -1)
  end
end