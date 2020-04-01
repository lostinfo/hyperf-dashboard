<template>
  <el-row>
    <el-row>
      <vue-table ref="table" :api-url="'/articles'" show-paginate can-export :search-model="searchModel"
                 :fields="fields"
                 :item-actions="itemActions" @table-action="tableActions">
        <template slot="header-title">
          <span>文章列表</span>
        </template>
        <template slot="header-button">
          <el-button type="primary" size="mini" icon="el-icon-circle-plus"
                     @click="$router.push({path:'/admin/article'})">添加
          </el-button>
        </template>
      </vue-table>
    </el-row>
  </el-row>
</template>

<script>
  export default {
    name: "Articles",
    data() {
      return {
        searchModel: {

        },
        fields: [
          {
            label: '#',
            key: 'id',
            sortable: true,
            width: 80,
          },
          {
            label: '标题',
            key: 'title',
            width: 400,
          },
          {
            label: '是否显示',
            width: 120,
            template: ({can_show}) => {
              return can_show ? "<span style='color: green;'>是</span>" : "<span style='color: grey;'>否</span>"
            }
          },
          {
            label: '首页推荐',
            width: 120,
            template: ({is_index}) => {
              return is_index ? "<span style='color: green;'>是</span>" : "<span style='color: grey;'>否</span>"
            }
          },
          {
            label: '排序',
            key: 'sort',
            sortable: true,
            width: 120,
          },
          {
            label: '创建时间',
            key: 'created_at',
          },
        ],
        itemActions: [
          {
            action: 'edit',
            type: 'primary',
            label: '编辑',
            permission: 'article.edit',
          },
          {
            action: 'delete',
            type: 'danger',
            label: '删除',
            permission: 'article.delete',
          },
        ],
      }
    },
    created() {

    },
    mounted() {

    },
    methods: {
      tableActions(action, item) {
        let that = this
        that[action + 'Action'](item)
      },
      showAction(item) {
        window.open('/article/' + item.id, '_black')
      },
      editAction(item) {
        this.$router.push({path: '/admin/article/' + item.id})
      },
      deleteAction(item) {
        let that = this
        that.$confirm('此操作将永久删除该数据, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          that.axios.delete('/articles/' + item.id).then(res => {
            that.$refs.table.loadData()
            that.$message.success('操作成功')
          }).catch(err => {
            console.log(err)
          })
        }).catch(() => {})
      },
    },
  }
</script>

<style scoped>

</style>
