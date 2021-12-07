FROM mdillon/postgis

RUN apt-get update && apt-get install -y \
	wget \
	g++ \
	pkg-config \
	libc++-dev \
	libc++abi-dev \
	build-essential \
	git

RUN wget https://github.com/plv8/plv8/archive/v2.3.15.tar.gz && \
	tar -xvzf v2.3.15.tar.gz && \
	cd plv8-2.3.15 && \
	echo "LIST FILES" && \
	ls && \
	make

RUN make install