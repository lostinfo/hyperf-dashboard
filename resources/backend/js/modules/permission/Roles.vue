<template>
  <el-row>
    <vue-table ref="table" :api-url="'/roles'" show-paginate :search-model="searchModel" :fields="fields"
               :item-actions="itemActions" @table-action="tableActions">
      <template slot="header-title">
        <span>角色列表</span>
      </template>
      <template slot="header-button">
        <el-button type="primary" size="mini" icon="el-icon-circle-plus" v-popover:role>添加</el-button>
        <el-popover
          ref="role"
          placement="bottom-start"
          title="选择用户组"
          width="200"
          trigger="click">
          <el-button type="primary" size="mini"
                     v-for="(guard, index) in guard_options"
                     :key="index"
                     @click="$router.push({path:'/admin/role', query: {guard_name: guard}})">
            {{guard}}
          </el-button>
        </el-popover>
      </template>
      <template slot="search-items">
        <el-form-item label="角色名称" prop="name">
          <el-input
            placeholder="角色名称"
            v-model="searchModel.name">
          </el-input>
        </el-form-item>
        <el-form-item label="用户组" prop="guard_name">
          <el-select v-model="searchModel.guard_name">
            <el-option
              v-for="(item, index) in guard_options"
              :key="index"
              :label="item"
              :value="item">
            </el-option>
          </el-select>
        </el-form-item>
      </template>
    </vue-table>
  </el-row>
</template>

<script>
  export default {
    name: "Roles",
    data() {
      return {
        searchModel: {
          name: '',
          guard_name: '',
        },
        fields: [
          {
            label: '#',
            key: 'id',
            sortable: true,
            width: 80
          },
          {
            label: '角色名称',
            key: 'name',
          },
          {
            label: '权限数量',
            key: 'permissions_count',
          },
          {
            label: '用户组',
            key: 'guard_name',
          },
          {
            label: '创建时间',
            key: 'created_at',
          },
        ],
        itemActions: [
          {
            action: 'edit',
            label: '编辑',
          },
          {
            action: 'delete',
            label: '删除',
          },
        ],
        guard_options: [],
      }
    },
    created() {

    },
    mounted() {
      this.getGuardOptions()
    },
    methods: {
      getGuardOptions() {
        let that = this
        that.axios.get('/option/auth/guards').then(res => {
          that.guard_options = res
        })
      },
      tableActions(action, item) {
        let that = this
        that[action + 'Action'](item)
      },
      editAction(item) {
        this.$router.push({path: '/admin/role/' + item.id})
      },
      deleteAction(item) {
        let that = this
        that.$confirm('此操作将永久删除该数据, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          that.axios.delete('/roles/' + item.id).then(res => {
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
