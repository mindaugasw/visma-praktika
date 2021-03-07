`docker build -t php_img .`

`docker run --rm -p 8000:80 --volume=$(pwd):/code php_img`