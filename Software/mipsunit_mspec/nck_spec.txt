describe :nCk do

  def nCk(n, k) 
    return 1 if k == 0
    return n if k == 1
    return 1 if n == k
    nCk(n-1, k-1) + nCk(n-1, k)
  end

  (0..20).each do |n|
    (0..n).each do |k|
      it "correctly calculates #{n} choose #{k}" do
        call n, k
        verify :v0 => nCk(n, k)
      end
    end
  end
end
