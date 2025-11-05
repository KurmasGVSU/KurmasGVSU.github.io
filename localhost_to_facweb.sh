# There was a reason this didn't intially have -name '*.html'; but, I don't remember what it is.
find . \( -type d -name .git -prune \) -o -type f -name '*.html' -print0 | xargs -0 sed -i 's/http:\/\/localhost:8000/https:\/\/faculty.computing.gvsu.edu\/kurmasz/g'
