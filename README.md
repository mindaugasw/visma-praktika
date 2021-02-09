# Visma praktika

Usage:
  
`php -f src/main.php -- args...`

Arguments:

Long | Short | Required? | Description  
--- | --- | --- | ---  
--command | -c | Required | Command to execute
--method | -m | Optional | Pattern search method to use. Valid values: *array, tree*.

Commands can have their own additional arguments.

### Commands
#### Interactive input

Process one word at a time. Also outputs matched patterns.

`... -- -c=interactive`  
`... -- -c=interactive -i=mistranslate` 

Long | Short | Required? | Description  
--- | --- | --- | ---  
--input | -i | Optional | Single word initial input


#### Text input
Process whole text block at once.

`... -- -c=text -i="If once you start down the dark path..."`  
`... -- -c=text -f="data/text-input-1.txt" -o="var/output/output-1.txt"`

Long | Short | Required? | Description  
--- | --- | --- | ---  
--input | -i | Optional | Piece of text: word, sentence, paragraph
--file | -f | Optional | File path for file input. Will have higher priority than --input
--output | -o | Optional | File path for file output

Either --input or --file must be set.

