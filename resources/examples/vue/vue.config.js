/*global module */
module.exports = {
  chainWebpack: config => {
    config.externals({
      idaas: "Idaas"
    });
  }
};
