use utf8;
use open ':encoding(utf8)';
use open ':std';
while (<>) {
	if (/\\input\{([^\{\}]+)\}/) {
		my $inputfile = $1;
		my $input;
		open(IN, "< $inputfile");
		while (my $line = readline(IN)) {
			$input .= $line;
		}
		close(IN);
		s/\\input\{([^\{\}]+)\}/$input/;
	}
	print;
}
