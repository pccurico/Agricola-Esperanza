from pathlib import Path
text = Path('app/Views/machinery.php').read_text(encoding='utf-8')
needle = 'create_maintenance'
idx = text.find(needle)
print('IDX', idx)
print(text[idx:idx+2500])
