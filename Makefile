release:
	ncc build --config="release"

install:
	ncc package install --package="build/release/net.nosial.tempfile.ncc" --skip-dependencies --reinstall -y

uninstall:
	ncc package uninstall -y --package="net.nosial.tempfile"