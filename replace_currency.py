import os
import re

count = 0
for root, dirs, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Replace \$ followed by {{ or literal $ followed by number_format
            # E.g. ${{ number_format(...) }} -> EGP {{ number_format(...) }}
            # \$([0-9]) -> EGP \1
            # \$([^A-Za-z0-9_]) -> This is tricky because of PHP variables $restaurant
            # The safest way is to use regex: replace \$ (?=\d|\{\{|number_format) with EGP
            # In blade templates, currency is usually:
            # ${{
            # $ {{
            # ${{number_format
            
            new_content = re.sub(r'\$(\{\{)', r'EGP \1', content)
            new_content = re.sub(r'>\$([0-9]+)', r'>EGP \1', new_content)
            new_content = re.sub(r'>\$(?![\w])', r'>EGP ', new_content)
            
            if content != new_content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                count += 1
                print(f"Updated {path}")

print(f"Total files updated: {count}")
