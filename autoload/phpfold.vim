function! phpfold#Fold(points) " {{{
	let step = <SID>getStepOutsideFold()
	for point in a:points
		let lineStart = <SID>getFarestSurroundingBlankLine(point[0], step)
		let lineStop = <SID>getFarestSurroundingBlankLine(point[1], step)
		if foldlevel(lineStart) != 0
			continue
		endif
		exec lineStart.','.lineStop.'fold'
	endfor
endfunction " }}}

function! s:getStepOutsideFold() "{{{
	if exists('g:phpfold_include_surround_blank_lines')
		return 'upward' is g:phpfold_include_surround_blank_lines ? -1 : 1
	endif

	return 0
endfunction	}}}"

function! s:getFarestSurroundingBlankLine(lineNumber, step)
		if 0 is a:step
			return a:lineNumber
		endif

    let step = a:step > 0 ? 1 : -1
    let extremalLineNumber = (step >= 0) ? line('$') : 1
    let currentLine = a:lineNumber

    while currentLine != extremalLineNumber && getline(currentLine + step) =~? '\v^\s*$'
        let currentLine += step
    endwhile

    return currentLine
endfunction

function! phpfold#PHPFoldText() " {{{
	let currentLine = v:foldstart
	let lines = (v:foldend - v:foldstart + 1)
	let lineString = getline(currentLine)
	" See if we folded a marker
	if strridx(lineString, "{{{") != -1 " }}}
		" Is there text after the fold opener?
		if (matchstr(lineString, '^.*{{{..*$') == lineString) " }}}
			" Then only show that text
			let lineString = substitute(lineString, '^.*{{{', '', 'g') " }}}
			" There is text before the fold opener
		else
			" Try to strip away the remainder
			let lineString = substitute(lineString, '\s*{{{.*$', '', 'g') " }}}
		endif
		" See if we folded a DocBlock
	elseif strridx(lineString, '#@+') != -1
		" Is there text after the #@+ piece?
		if (matchstr(lineString, '^.*#@+..*$') == lineString)
			" Then show that text
			let lineString = substitute(lineString, '^.*#@+', '', 'g') . ' ' . g:phpDocBlockIncludedPostfix
			" There is nothing?
		else
			" Use the next line..
			let lineString = getline(currentLine + 1) . ' ' . g:phpDocBlockIncludedPostfix
		endif
		" See if we folded an API comment block
	elseif strridx(lineString, "\/\*\*") != -1
		" (I can't get search() or searchpair() to work.., therefore the
		" following loop)
		let s:state = 0
		while currentLine < v:foldend
			let line = getline(currentLine)
			if s:state == 0 && strridx(line, "\*\/") != -1
				" Found the end, now we need to find the first not-empty line
				let s:state = 1
			elseif s:state == 1 && (matchstr(line, '^\s*$') != line)
				" Found the line to display in fold!
				break
			endif
			let currentLine = currentLine + 1
		endwhile
		let lineString = getline(currentLine)
	endif

	" Some common replaces...
	" if currentLine != v:foldend
	let lineString = substitute(lineString, '/\*\|\*/\d\=', '', 'g')
	let lineString = substitute(lineString, '^\s*\*\?\s*', '', 'g')
	let lineString = substitute(lineString, '{$', '', 'g')
	let lineString = substitute(lineString, '($', '(..);', 'g')
	" endif

	" Emulates printf("%3d", lines)..
	if lines < 10
		let lines = "  " . lines
	elseif lines < 100
		let lines = " " . lines
	endif

	" Append an (a) if there is PhpDoc in the fold (a for API)
	if currentLine != v:foldstart
		let lineString = lineString . " " . g:phpDocIncludedPostfix . " "
	endif

	" Return the foldtext
	return "+--".lines." lines: " . lineString
endfunction " }}}
" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
