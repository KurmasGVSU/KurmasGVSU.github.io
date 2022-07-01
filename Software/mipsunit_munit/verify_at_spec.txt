describe :some_function do
  it "sets $at to 6" do
    call
    @output.puts "mv $t3, $at"
    verify(:t3, 6)
  end
end