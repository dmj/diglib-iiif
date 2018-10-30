# HAB Diglib IIIF

HAB Diglib IIIF is copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel and released under
the terms of the GNU Affero General Public License.

## About

A PHP web application that implements the IIIF Presentation and the IIIF Image API using METS
document as data source.  The application does not provide an in-memory representation of the IIIF
entities but uses a mapper to retrieve the requested information from the METS file.

## Installation

You can install this application by cloning the repository.

```
git clone https://github.com/dmj/diglib-iiif.git
```

### Mapping

<table>
  <tbody>
    <tr>
      <th>Manifest</th>
      <td>mets:mets/@⁠OBJID</td>
      <td>required</td>
    </tr>
    <tr>
      <th>Sequence</th>
      <td>mets:div[@⁠TYPE = 'physSequence']/@⁠ID</td>
      <td>optional</td>
    </tr>
    <tr>
      <th>Annotation</th>
      <td>mets:div[@⁠TYPE = 'page']/mets:fptr/@⁠ID</td>
      <td>optional</td>
    </tr>
    <tr>
      <th>Canvas</th>
      <td>mets:div[@⁠TYPE = 'page']/@⁠ID</td>
      <td>optional</td>
    </tr>
    <tr>
      <th>Image</th>
      <td>mets:fileGrp[@⁠USE = 'MASTER']/mets:file/@⁠ID</td>
      <td>optional</td>
    </tr>
  </tbody>
</table>

## Limitations

Currently the web application has the following limitations:

- does not create manifest metadata
- the METS application profile is not documented yet and might be subject to change
- uses a stub-resolver that always points to the example directory
