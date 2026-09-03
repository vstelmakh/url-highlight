# Benchmark inputs

All input files hold the same prose. They differ in URL density, in how much markup wraps it, and in how it is encoded.
Bytes and URLs come from the file. Words and Tags come from the prose, before markup and encoding.

| Input                | Bytes   | Words | URLs | Tags |
|----------------------|---------|-------|------|------|
| `plain_no_urls`      | 24313   | 4415  | 0    | 0    |
| `plain_low_urls`     | 25206   | 4467  | 23   | 0    |
| `plain_medium_urls`  | 26596   | 4476  | 69   | 0    |
| `plain_high_urls`    | 31376   | 4461  | 231  | 0    |
| `html_light_markup`  | 26879   | 4476  | 69   | 94   |
| `html_medium_markup` | 33720   | 4476  | 69   | 774  |
| `html_heavy_markup`  | 53578   | 4476  | 69   | 3156 |
| `html_special_chars` | 40388   | 4476  | 69   | 774  |
| `html_entities`      | 199187  | 4476  | 69   | 774  |

The files are not the same size, so read a mean together with the byte column. For example `html_entities` is about
five times slower than `html_medium_markup`, but it is also about six times bigger for the same text. That gap is
size, not the cost of decoding.
