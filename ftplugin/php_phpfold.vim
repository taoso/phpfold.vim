let s:save_cpo = &cpo
set cpo&vim

setlocal foldmethod=manual
setlocal foldtext=phpfold#PHPFoldText()

function! s:doFold(status, response)
	let points = json_decode(a:response)
	call phpfold#Fold(points)
	normal! zv
endfunction

let s:folder_path = 'php '.expand('<sfile>:p:h:h').'/php/phpfold.php'
function! s:fold()
	let php_path = expand('%:p')
	let cmd = s:folder_path.' '.php_path
	if has('job')
		let job = job_start(cmd, {'out_cb': function('s:doFold')})
	else
		let job = jobstart(cmd, {'on_stdout': function('s:doFold')})
	endif
endfunction

command! -nargs=0 PhpFold call s:fold()
nnoremap <buffer> zm :PhpFold<CR>

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
