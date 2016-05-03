let s:save_cpo = &cpo
set cpo&vim

setlocal foldmethod=manual
setlocal foldtext=phpfold#PHPFoldText()

if !exists('g:phpfold_channel_id')
	let s:phpfold_path = expand('<sfile>:p:h:h') . '/php/phpfold.php'
	let g:phpfold_channel_id = rpcstart('php', [s:phpfold_path])
endif

function! s:fold()
	call rpcnotify(g:phpfold_channel_id, 'fold', expand('%:p'))
endfunction

command! -nargs=0 PhpFold call s:fold()

autocmd! BufWinEnter *.php call s:fold()
nnoremap <buffer> zm :PhpFold<CR>

let &cpo = s:save_cpo
unlet s:save_cpo

" vim: foldmethod=marker:noexpandtab:ts=2:sts=2:sw=2
