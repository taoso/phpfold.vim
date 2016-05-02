function! phpfold#Fold(points) " {{{
	for point in a:points
		let lineStart = point[0]
		let lineStop = point[1]
		let cur_line = line('.')
		if cur_line >= lineStart && cur_line <= lineStop
			continue
		endif
		if foldlevel(lineStart) != 0
			if foldclosedend(lineStart) == lineStop
				continue
			endif
		endif
		exec lineStart.",".lineStop."fold"
	endfor
endfunction " }}}

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
