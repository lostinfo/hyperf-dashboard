<template>
  <el-row>
    <el-card class="view-card">
      <el-form class="admin-form" :model="articleModel" :rules="articleRules" ref="formRef" label-width="120px" v-loading="formLoading">
        <el-form-item label="标题" prop="title">
          <el-input v-model="articleModel.title"></el-input>
        </el-form-item>
        <el-form-item label="副标题" prop="subtitle">
          <el-input v-model="articleModel.subtitle" type="textarea" rows="2"></el-input>
        </el-form-item>
        <el-form-item label="封面" prop="poster">
          <el-upload
            class="el-uploader poster"
            action="-"
            :show-file-list="false"
            :http-request="handlePosterUpload">
            <img v-if="articleModel.poster" :src="articleModel.poster" class="el-uploader-image">
            <i v-else class="el-icon-plus el-uploader-icon"></i>
            <div class="el-upload__tip" slot="tip">只能上传image文件</div>
          </el-upload>
        </el-form-item>
        <el-form-item label="是否显示" prop="can_show">
          <el-checkbox v-model="articleModel.can_show"></el-checkbox>
        </el-form-item>
        <el-form-item label="首页推荐" prop="is_index">
          <el-checkbox v-model="articleModel.is_index"></el-checkbox>
        </el-form-item>
        <el-form-item prop="sort">
          <template slot="label">
            排序
            <span>
              <el-tooltip class="item" effect="dark" content="数字越小越靠前" placement="top">
                <i class="fa fa-question-circle"></i>
              </el-tooltip>
            </span>
          </template>
          <el-input v-model.number="articleModel.sort"></el-input>
        </el-form-item>
        <el-form-item label="正文" prop="markdown">
          <vue-edit ref="editRef" :markdown="articleModel.markdown" :upload-url="'/files/article'"></vue-edit>
        </el-form-item>
        <el-form-item>
          <el-button @click="submitClose">取消</el-button>
          <el-button type="primary" @click="submit">提交</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </el-row>
</template>

<script>
  export default {
    name: "Article",
    data() {
      return {
        formLoading: false,
        id: null,
        articleModel: {
          title: '',
          subtitle: '',
          poster: '',
          can_show: true,
          is_index: true,
          markdown: '',
          content: '',
          sort: '',
        },
        articleRules: {
          title: [
            {required: true, message: '请输入标题', trigger: 'blur'}
          ],
          subtitle: [
            {required: true, message: '请输入副标题', trigger: 'blur'}
          ],
          poster: [
            {required: true, message: '请上传封面', trigger: 'blur'}
          ],
          markdown: [
            {required: true, message: '请输入正文', trigger: 'blur'}
          ],
          sort: [
            {required: true, message: '请输入排序值', trigger: 'blur'},
            {type: 'number', message: '请输入数字', trigger: 'blur'}
          ],
        }
      }
    },
    created() {

    },
    mounted() {
      if (this.$route.params.hasOwnProperty('id')) {
        this.id = this.$route.params.id
        this.getArticle()
      }
    },
    methods: {
      getArticle() {
        let that = this
        that.formLoading = true
        that.axios.get('/articles/' + that.id).then(res => {
          that.formLoading = false
          that.articleModel = res
          that.$refs.editRef.setSimplemdeValue(that.articleModel.markdown)
        }).catch(err => {
          that.formLoading = false
        })
      },
      handlePosterUpload(source) {
        let that = this
        let formData = new FormData()
        formData.append('file', source.file)
        that.axios.post('/files/article', formData).then(res => {
          that.articleModel.poster = res.url
        })
      },
      submitClose() {
        let that = this
        that.$router.back()
      },
      submit() {
        let that = this
        that.formLoading = true
        that.articleModel.markdown = that.$refs.editRef.getSimplemdeMarkdown()
        that.articleModel.content = that.$refs.editRef.getSimplemdeHtml()
        that.$refs.formRef.validate((valid) => {
          if (!valid) {
            that.formLoading = false
            return false
          }
          that.axios.post('/articles', that.articleModel).then(res => {
            that.formLoading = false
            that.$message.success('提交成功')
            setTimeout(function () {
              that.$router.replace('/admin/articles')
            }, 2000)
          }).catch(err => {
            that.formLoading = false
          })
        })
      }
    },
  }
</script>

<style>
  .el-uploader.poster .el-upload, .el-uploader.poster .el-uploader-image, .el-uploader.poster .el-uploader-icon {
    width: 480px;
    height: 270px;
  }

  .el-uploader.poster .el-uploader-icon {
    line-height: 270px;
  }
</style>
