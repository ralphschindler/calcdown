# Readme

Calcdown is a DSL for defining and running calculations in a
markdown-like format. It allows users to write calculations in
a human-readable way, making it easy to document and share
computational workflows.

It is insipred by Numi.app and other similar tools. Given a multi
line string like this:

```
# Calculate the area of a circle
radius = 5 cm
area = π * radius^2
area in m^2
```

Calcdown can parse and evaluate the calculations, producing
the result along with a detailed breakdown of the steps taken.

