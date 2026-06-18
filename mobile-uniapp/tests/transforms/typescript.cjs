const ts = require('typescript')

module.exports = {
  process(sourceText, sourcePath) {
    const output = ts.transpileModule(sourceText, {
      compilerOptions: {
        esModuleInterop: true,
        module: ts.ModuleKind.CommonJS,
        sourceMap: true,
        target: ts.ScriptTarget.ES2020,
      },
      fileName: sourcePath,
    })

    return {
      code: output.outputText,
      map: output.sourceMapText,
    }
  },
}
