# phpfold

Fold according php ast. Thanks to [PHP Parser](https://github.com/nikic/PHP-Parser).

# install

For vim-plug:
```
Plug 'lvht/phpfold.vim', { 'for': 'php' }
```

# usage
Use <kbd>zm</kbd> or <kbd>:PhpFold<CR></kbd> to fold the php file.

Set
```
let g:phpfold_include_surround_blank_lines = 'downward'
```
if you want to include surround blank lines into each fold. You can set it also to `upward`.
